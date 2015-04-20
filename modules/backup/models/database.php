<?php


class databaseModelBup extends modelBup
{

    /** @var array */
    protected $config;
    private $use_mysqli = false;
	private $insert_limit = 1000;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (function_exists('mysqli_connect')) {
            if (defined('WP_USE_EXT_MYSQL')) {
                $this->use_mysqli = !WP_USE_EXT_MYSQL;
            } elseif (version_compare(
                    phpversion(),
                    '5.5',
                    '>='
                ) || !function_exists('mysql_connect')
            ) {
                $this->use_mysqli = true;
            } elseif (false !== strpos($GLOBALS['wp_version'], '-')) {
                $this->use_mysqli = true;
            }
        }
    }


    /**
     * Create database backup
     * @param string $filename Path and file name of backup
     * @return bool TRUE if dump successfully created, FALSE otherwise.
     */
    public function create($filename)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        global $wp_db_version;
        global $wp_version;

		$dumpHandle = fopen($filename, 'a');
        $database = array(
            sprintf('-- Created with %s %s', BUP_S_WP_PLUGIN_NAME, BUP_VERSION),
            '-- http://supsystic.com/plugins/backup-plugin/' . PHP_EOL,
            sprintf('-- Do not change these values if you doesnt want broke the database during recovery:'),
            sprintf('-- @dbrev=%s;', $wp_db_version),        // database revision
            sprintf('-- @wpcrv=%s;', $wp_version),           // wordpress verison
            sprintf('-- @plgnv=%s;', BUP_VERSION) . PHP_EOL, // plugin version
        );
        $database = dispatcherBup::applyFilters('changeDBDumpHeader', $database);
		fwrite($dumpHandle, implode(PHP_EOL, $database));
        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        foreach($tables as $table) {
            $table['name'] = $table[0];
            //$table['insert'] = '';

            // Drop table query
            //$table['drop'] = 'DROP TABLE IF EXISTS `'.$table['name'].'`#endQuery' . PHP_EOL;
            $dropTableQuery = 'DROP TABLE IF EXISTS `'.$table['name'].'`#endQuery' . PHP_EOL;
            $dropTableQuery = dispatcherBup::applyFilters('encryptDbData', $dropTableQuery);
			fwrite($dumpHandle, $dropTableQuery);
            // Create table query
            $createQuery = $wpdb->get_row('SHOW CREATE TABLE `'.$table['name'].'`', ARRAY_A);
            if(isset($createQuery['Create Table'])) {
                $createTableQuery = $createQuery['Create Table'] . '#endQuery' . PHP_EOL;
                $createTableQuery = dispatcherBup::applyFilters('encryptDbData', $createTableQuery);
				fwrite($dumpHandle, $createTableQuery);
                //$table['create'] = $createQuery['Create Table'] . '#endQuery' . PHP_EOL;
            }

            // Get table columns
            $tableColsQuery = 'SHOW COLUMNS FROM `'.$table['name'].'`';

            // Parse columns
            $tableCols = $this->parseColumns($wpdb->get_results($tableColsQuery, ARRAY_A));

			// Get table data
			$tableRowsNum = (int) $wpdb->get_var('SELECT COUNT(*) AS total FROM `'.$table['name'].'`');
			if($tableRowsNum) {
				for($i = 0; $i < $tableRowsNum; $i += $this->insert_limit) {
					$tableDataQuery = 'SELECT * FROM `'.$table['name'].'` LIMIT '. $i. ', '. $this->insert_limit. '';
					$tableData = $this->parseData($wpdb->get_results($tableDataQuery, ARRAY_N));
					/*$insertData = array();
					foreach($tableData as $data) {
						$table['insert'] .= 'INSERT INTO `' . $table['name'] . '` (' . implode(', ', array_map(array($this, 'addColQuotes'), $tableCols)) . ') VALUES (' . $data . ')' . '#endQuery' . PHP_EOL;
					}*/
					//$table['insert'] .= 'INSERT INTO `' . $table['name'] . '` (' . implode(', ', array_map(array($this, 'addColQuotes'), $tableCols)) . ') VALUES ('. implode('),(', $tableData). ');';
					$insertQuery = 'INSERT INTO `' . $table['name'] . '` (' . implode(', ', array_map(array($this, 'addColQuotes'), $tableCols)) . ') VALUES ('. implode('),/*BUP_EOL*/(', $tableData). ');' . '#endQuery'. PHP_EOL;
					$insertQuery = dispatcherBup::applyFilters('encryptDbData', $insertQuery);
                    fwrite($dumpHandle, $insertQuery);
				}
			}

            // Push results to stack
            //$database[] = implode(PHP_EOL, array($table['drop'], $table['create'], isset($table['insert']) ? $table['insert'] : ''));
        }
		fclose($dumpHandle);
        /*if (file_put_contents($filename, implode(PHP_EOL, $database))) {
            return true;
        }*/

        return true;
    }

	private function _flexRestore($filename) {
		$res = true;
		$permitted = array('CREATE', 'INSERT', 'DROP');
		$query = '';
		$allMetadata = array();
		$metaDataValidated = false;
		$config = $this->getConfig();
		$transaction = $this->getTransactionSupport();
		$handle = fopen($filename, 'r');
		$handle = dispatcherBup::applyFilters('decryptDbData', $handle, $filename);
        $decryptKeyExist = dispatcherBup::applyFilters('checkIsSecretKeyToDecryptDBExist', false);
		$queriesStarted = false;
        $iteratorEmptyRow = 0; // This iterator used to control decrypted data, if data not decrypted - all query will be empty, well then secret key is wrong
        while(($row = fgets($handle)) !== false) {	// Parse file row-by-row as it can be too large to store all data - in memory in one time
			$row = trim($row);
            $iteratorEmptyRow++;
			if(preg_match('/--\s*@(.*);/', $row, $metadata)) {	// At first - let's find metadata, it should be in the begining of file
				$metaKeyValue = array_map('trim', explode('=', $metadata[1]));
				$allMetadata[ $metaKeyValue[0] ] = $metaKeyValue[1];
				continue;
			}
			if(!$metaDataValidated) {	// If metadata was not validated - do not go futher
				if(empty($allMetadata))	// At the begining of our sql file - no metadata for now, so just continue to search
					continue;
				if($this->validateMetadata($allMetadata) === false) {	// Then - try to validate metadata
					$res = false;
					break;
				} else
					$metaDataValidated = true;	// Validated - can go futher
			}
			if(!$queriesStarted) {	// This code should be executed only when queries part started
				if(!$this->_startQueries($config, $transaction))
					break;
				$queriesStarted = true;
			}
			$rowAplied = false;
			foreach($permitted as $canStartQuery) {
				if(strpos($row, $canStartQuery) === 0) {	// Check - if this is start of our query
					if(!empty($query)) {	// If we have some not executed query (in some case) - execute it
						$this->_runQuery($query, $config, $transaction);
                        $iteratorEmptyRow = 0;
					}
					$query = $row;
					$rowAplied = true;
					break;
				}
			}
			if(!empty($query) && !$rowAplied) {	// Just continue to compose query if it was already started
				$query .= $row;
			}
			if(strpos($row, '#endQuery')) {	// If this is end of query - just execute it and prepare for next one
				$this->_runQuery($query, $config, $transaction);
                $iteratorEmptyRow = 0;
				$query = '';
			}
            if(empty($query) && empty($row) && $decryptKeyExist && $iteratorEmptyRow > 20){
                do_action('bupClearSecretKeyToDecryptDb');
                return array('error' => 'Secret key for decrypt DB data wrong! Please, try again.');
            }
		}
		if($queriesStarted) {	// If queries was started - then let's finish it's execution correctly
			$this->_endQueries($config, $transaction);
		}
		fclose($handle);
        if($this->haveErrors())
            return false;
	}
	private function _startQueries($config, $transaction) {
		// Start transaction if safe update enabled or set error if transaction is unsupported
        if($config['safe_update'] === true) {
            if($transaction === true) {
                $this->query('SET AUTOCOMMIT=0');
                $this->query('START TRANSACTION');
            }
            else {
                $this->pushError(langBup::_('Your MySQL server does not support transactions, "safe update" unavailable'));
                return false;
            }
        }
		$this->query('SET FOREIGN_KEY_CHECKS=0');

		return true;
	}
	private function _endQueries($config, $transaction) {
		 // Commit transaction if safe update enabled
        global $wpdb;
        if($config['safe_update'] === true && $transaction === true) {
            $this->query('COMMIT');
        }

        $this->query('SET FOREIGN_KEY_CHECKS=1', $wpdb->dbh);
	}
	private function _runQuery($query, $config, $transaction) {
		/** @var wpdb $wpdb */
        global $wpdb;

        if(empty($query) OR $query === null) {
            return false;
        }

		if($wpdb->query($query) === false) {
			// Rollback transaction if safe update enabled
			if($config['safe_update'] === true && $transaction === true) {
				$this->query('ROLLBACK');
			}

			$this->query('SET FOREIGN_KEY_CHECKS=1');
			return false;
		}

        return true;
	}
    /**
     * Restore database backup
     * @param string $filename Path and file name of database backup
     * @return bool TRUE if okay, FALSE otherwise.
     */
    public function restore($filename)
    {
        global $wpdb;

        if(file_exists($filename) === false) {
            $this->pushError(sprintf('Backup %s doesn\'t exists', basename($filename)));
            return false;
        }
		// New way to execute database restoration - to not get all file to memory, but execute it row-by-row
		return $this->_flexRestore($filename);
		/*
        // Read SQL file
        $input = file_get_contents($filename);

        // Validate metadata
        $metadata = $this->parseMetadata($input);
        if($this->validateMetadata($metadata) === false) {
            return false;
        }

        // Is it old backup?
        if (preg_match('/(database|full)/ui', $filename)) {
            return $this->restoreFallback($filename);
        }

        // Parse queries
        $queries = $this->parseQueries($input);
        if($queries === null) {
            $this->pushError('Unable to parse queries from SQL file');
            return false;
        }

        // Run parsed queries
        if($this->runQueries($queries) === false) {
            $this->pushError($wpdb->last_error);
            return false;
        }

        return true;*/
    }

    public function restoreFallback($source)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $ret = true;
        if ($handle = @fopen($source, "r")) {
            if ($this->clearFallback($source)) {
                while (($buffer = fgets($handle)) !== false) {
                    $wpdb->query($buffer);
                }

                if (!feof($handle)) {
                    $this->pushError(langBup::_("Error: unexpected fgets() fail"));
                }

                fclose($handle);
            } else {
                $this->pushError(langBup::_('Unable to clear the database before restoring'));
                $ret = false;
            }
        } else {
            $this->pushError(langBup::_(sprintf('File not found: %s', $source)));
            $ret = false;
        }

        return $ret;
    }

    public function clearFallback($source)
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $ret = true;
        if ($handle = @fopen($source, "r")) {
            $i = 0;
            while (($buffer = fgets($handle)) !== false) {
                $i++;
                if ($i <= 4) continue;

                preg_match_all('~CREATE TABLE `(.*)` \( ~', $buffer, $out);
                if ($out[1][0]) {
                    $wpdb->query("DROP TABLE {$out[1][0]};");
                } else
                    break;
            }
        } else {
            $this->pushError(langBup::_('Can not find file '.$source));
            $ret = false;
        }
        return $ret;
    }

    /**
     * Validate metadata of SQL file and stop restore if its invalid
     * @param  array $metdata
     * @return boolean
     */
    protected function validateMetadata($metdata) {
        global $wp_db_version;
        global $wp_version;

        $config = $this->getConfig();
        // If force update enabled then we does not have any reasons to validate metadata
        if($config['force_update'] === true) {
            return true;
        }

        if($metdata['dbrev'] != $wp_db_version) {
            $this->pushError(
                langBup::_('Revision of backup and your database do not match. '
                    . 'You must enable the Force Update options to update '
                    . '(at one\'s own risk)')
            );
            return false;
        }

        if($metdata['wpcrv'] != $wp_version) {
            $this->pushError(
                langBup::_('This backup was made on another version of WordPress. '
                    . 'You must enable the Force Update options to update '
                    . '(at one\'s own risk)')
            );
            return false;
        }

        if($metdata['plgnv'] != BUP_VERSION) {
            $this->pushError(
                langBup::_('Backup was created with a different version of the plugin. '
                    . 'You must enable the Force Update options to update '
                    . '(at one\'s own risk)')
            );
            return false;
        }

        return true;
    }

    /**
     * Run parsed queries from SQL file
     * @global \wpdb   $wpdb
     * @param  array   $queries
     * @return boolean
     */
    protected function runQueries($queries = array()) {
        /** @var wpdb $wpdb */
        global $wpdb;

        $config = $this->getConfig();
        $transaction = $this->getTransactionSupport();

        if(empty($queries) OR $queries === null) {
            return false;
        }

        // Start transaction if safe update enabled or set error if transaction is unsupported
        if($config['safe_update'] === true) {
            if($transaction === true) {
                $this->query('SET AUTOCOMMIT=0');
                $this->query('START TRANSACTION');
            }
            else {
                $this->pushError(langBup::_('Your MySQL server does not support transactions, "safe update" unavailable'));
                return false;
            }
        }

        $this->query('SET FOREIGN_KEY_CHECKS=0');


        foreach($queries as $query) {
            if($wpdb->query($query) === false) {
                // Rollback transaction if safe update enabled
                if($config['safe_update'] === true && $transaction === true) {
                    $this->query('ROLLBACK');
                }

                $this->query('SET FOREIGN_KEY_CHECKS=1');
                return false;
            }
        }

        // Commit transaction if safe update enabled
        if($config['safe_update'] === true && $transaction === true) {
            $this->query('COMMIT');
        }

        $this->query('SET FOREIGN_KEY_CHECKS=1', $wpdb->dbh);

        return true;
    }

    /**
     * Check for MySQL server verion, db handler access and other
     * @global wpdb $wpdb
     * @return boolean
     */
    public function getTransactionSupport() {
        /** @var wpdb $wpdb */
        global $wpdb;

        if (!$this->use_mysqli) {

            if (!function_exists('mysql_query') && function_exists(
                    'mysql_get_server_info'
                )
            ) {
                return false;
            }

            // Can we get access to the database handler?
            if (is_resource($wpdb->dbh) === false) {
                return false;
            }

            // Is it 5.x.x version?
            $serverInfo = mysql_get_server_info($wpdb->dbh);
            if (substr($serverInfo, 0, 1) != 5) {
                return false;
            }

            return true;
        }

        if (!function_exists('mysqli_get_server_info')) {
            return false;
        }

        $serverInfo = mysqli_get_server_info($wpdb->dbh);
        if (substr($serverInfo, 0, 1) != 5) {
            return false;
        }

        return true;
    }

    /**
     * Parse matrix of columns to the one dimensional array
     * @param  array $columns
     * @return array
     */
    protected function parseColumns($columns) {
        $result = array();

        foreach($columns as $column) {
            $result[] = $column['Field'];
        }

        return $result;
    }

    /**
     * Parse columns data from matrix to the one dimensional array
     * @param  array $data
     * @return array
     */
    protected function parseData($data) {
        $result = array();

        foreach($data as $column) {
            $values = array_map(array($this, 'addQuotes'), array_values($column));

            $result[] = implode(', ', $values);
        }

        return $result;
    }

    /**
     * Parse queries from sql file
     * @param  string $input
     * @return null|array
     */
    protected function parseQueries($input) {
        $queries = array();
        $permitted = 'CREATE|INSERT|DROP';

        // We parse only permitted queries from file
        preg_match_all('/(('.$permitted.')(.*?))#endQuery/su', $input, $queries);

        if(isset($queries[1]) && !empty($queries[1])) {
            return $queries[1];
        }

        return null;
    }

    /**
     * Parse metadata from SQL file
     * @param  string $input
     * @return null|array
     */
    protected function parseMetadata($input) {
        $metadata = array();
        preg_match_all('/--\s*@(.*);/', $input, $metadata);

        if(isset($metadata[1]) && !empty($metadata[1])) {
            $variables = array();
            // Create an array with key => val from strings with key=val
            foreach($metadata[1] as $variableType) {
                $e = explode('=', $variableType);
                $variables[trim($e[0])] = trim($e[1]);
            }
            return $variables;
        }

        return null;
    }

    /**
     * Add quotes to string values
     * @param  string $value
     * @return string|integer
     */
    public function addQuotes($value) {
        /** @var wpdb $wpdb */
        global $wpdb;

        $value = "'". $wpdb->_real_escape($value) . "'";

        return $value;
    }

    public function addColQuotes($col)
    {
        return '`' . $col . '`';
    }

    /**
     * Set an array of configurations
     * @param array $config
     * @return databaseModelBup
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Returns an array of configurations
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Executes the query
     *
     * @param string $query
     */
    public function query($query)
    {
        if (!$this->use_mysqli) {

            $this->mysqlQuery($query);

        } else {

            $this->mysqliQuery($query);
        }
    }

    protected function mysqlQuery($query)
    {
        global $wpdb;

        if (is_resource($wpdb->dbh) && function_exists('mysql_query')) {
            @mysql_query($query, $wpdb->dbh);

            return;
        }

        wp_die('Fatal error: Wordpress uses mysql, but extension does not exists.');
    }

    protected function mysqliQuery($query)
    {
        global $wpdb;

        if (function_exists('mysqli_query')) {
            @mysqli_query($wpdb->dbh, $query);

            return;
        }

        wp_die('Fatal error: Wordpress uses mysqli, but extension does not exists.');
    }
}
