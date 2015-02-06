<?php
/**
 * Class to adapt field before display
 * return ONLY htmlParams property
 * @see field
 */
class fieldAdapterBup {
    const DB = 'dbBup';
    const HTML = 'htmlBup';
    const STR = 'str';
    static public $userfieldDest = array('registration', 'shipping', 'billing');
    static public $countries = array();
    static public $states = array();
    /**
     * Executes field Adaption process
     * @param object type field or value $fieldOrValue if DB adaption - this must be a value of field, elase if html - field object
     */
    static public function _($fieldOrValue, $method, $type) {
        if(method_exists('fieldAdapterBup', $method)) {
            switch($type) {
                case self::DB:
                    return self::$method($fieldOrValue);
                    break;
                case self::HTML:
                    self::$method($fieldOrValue);
                    break;
                case self::STR:
                    return self::$method($fieldOrValue);
                    break;
            }
        }
        return $fieldOrValue;
    }
    static public function userFieldDestHtml($field) {
        $field->htmlParams['optionsBup'] = array();
        if(!is_array($field->value)) {
            if(empty($field->value)) 
                $field->value = array();
            else
                $field->value = json_decode($field->value);
        }
        foreach(self::$userfieldDest as $d) {
            $field->htmlParams['optionsBup'][] = array(
                'id' => $d,
                'text' => $d,
                'checked' => in_array($d, $field->value)
            );
        }
    }
    static public function userFieldDestToDB($value) {
        return utilsBup::jsonEncode($value);
    }
    static public function userFieldDestFromDB($value) {
        return utilsBup::jsonDecode($value);
    }
    static public function taxDataHtml($field) {
        $listOfDest = array();
        if(!is_array($field->value)) {
            if(empty($field->value)) 
                $field->value = array();
            else
                $field->value = (array)json_decode($field->value, true);
        }
        foreach(self::$userfieldDest as $d) {
            $listOfDest[] = array(
                'id' => $d,
                'text' => $d,
                'checked' => (is_array($field->value['dest']) && in_array($d, $field->value['dest']))
            );
        }
        $categories = frameBup::_()->getModule('products')->getCategories();
        $brands = frameBup::_()->getModule('products')->getBrands();
        $cOptions = array();
        $bOptions = array();
        if(!empty($categories)) {
            if(!is_array($field->value['categories']))
                    $field->value['categories'] = array();
            foreach($categories as $c) {
                $cOptions[] = array('id' => $c->term_taxonomy_id, 
                    'text' => $c->cat_name,
                    'checked' => in_array($c->term_taxonomy_id, $field->value['categories']));
            }
        }
        if(!empty($brands)) {
            if(!is_array($field->value['brands']))
                    $field->value['brands'] = array();
            foreach($brands as $b) {
                $bOptions[] = array('id' => $b->term_taxonomy_id, 
                    'text' => $b->cat_name,
                    'checked' => in_array($b->term_taxonomy_id, $field->value['brands']));
            }
        }
        return '<div>'. langBup::_('Apply To'). '
            <div id="tax_address">
                <b>'. langBup::_('Address'). '</b><br />
                '. langBup::_('Destination'). ':'. htmlBup::checkboxlist('params[dest]', array('optionsBup' => $listOfDest)). '<br />
                '. langBup::_('Country'). ':'. htmlBup::countryList('params[country]', array('notSelected' => true, 'value' => $field->value['country'])). '<br />
            </div>
            <div id="tax_category">
                <b>'. langBup::_('Categories'). '</b><br />
                '. (empty($cOptions) ? langBup::_('You have no categories') : htmlBup::checkboxlist('params[categories][]', array('optionsBup' => $cOptions))). '<br />
                    <b>'. langBup::_('Brands'). '</b><br />
                '. (empty($bOptions) ? langBup::_('You have no brands') : htmlBup::checkboxlist('params[brands][]', array('optionsBup' => $bOptions))). '<br />
            </div>
            <div>'. langBup::_('Tax Rate').': '. htmlBup::text('params[rate]', array('value' => $field->value['rate'])).'</div>
            <div>'. langBup::_('Absolute').': '. htmlBup::checkbox('params[absolute]', array('checked' => $field->value['absolute'])).'</div>
        </div>';
    }
    static public function displayCountry($cid, $key = 'name') {
        if($key == 'name') {
            $countries = self::getCountries();
            return $countries[$cid];
        } else {
            if(empty(self::$countries))
                self::$countries = self::getCachedCountries();
            foreach(self::$countries as $c) {
                if($c['id'] == $cid)
                    return $c[ $key ];
            }
        }
        return false;
    }
    static public function displayState($sid, $key = 'name') {
        $states = self::getStates();
        return empty($states[$sid]) ? $sid : $states[$sid][$key];
    }
    static public function getCountries($notSelected = false) {
        static $options = array();
        if(empty($options[ $notSelected ])) {
			$options[ $notSelected ] = array();
            if(empty(self::$countries))
                self::$countries = self::getCachedCountries();
            if($notSelected) {
				$options[ $notSelected ][0] = is_bool($notSelected) ? langBup::_('Not selected') : langBup::_($notSelected);
			}
            foreach(self::$countries as $c) $options[ $notSelected ][$c['id']] = $c['name'];
        }
        return $options[ $notSelected ];
    }
    static public function getStates($notSelected = false) {
        static $options = array();
        if(empty($options[ $notSelected ])) {
			$options[ $notSelected ] = array();
            if(empty(self::$states))
                self::$states = self::getCachedStates();
            if($notSelected) {
				$notSelectedLabel = is_bool($notSelected) ? 'Not selected' : $notSelected;
				$options[ $notSelected ][0] = array('name' => langBup::_( $notSelectedLabel ), 'country_id' => NULL);
			}
            foreach(self::$states as $s) $options[ $notSelected ][$s['id']] = $s;
        }
        return $options[ $notSelected ];
    }
    /**
     * Function to get extra field options 
     * 
     * @param object $field
     * @return string 
     */
    static public function getExtraFieldOptions($field_id) {
        $output = '';
        if ($field_id == 0) return '';
        $options = frameBup::_()->getModule('optionsBup')->getHelper()->getOptions($field_id);
        if (!empty($options)) {
            foreach ($options as $key=>$value) {
                $output .= '<p>'.$value.'<span class="delete_option" rel="'.$key.'"></span></p>';
            }
        }
        return $output;
    }
    /**
     * Function to get field params
     * 
     * @param object $params 
     */
    static public function getFieldAttributes($params){
        $output = '';
        if (!empty($params->attr)) {
            foreach ($params->attr as $key=>$value) {
                $output .= langBup::_($key).':<br />';
                $output .= htmlBup::text('params[attr]['.$key.']',array('value'=>$value)).'<br />';
            }
        } else {
                $output .= langBup::_('class').':<br />';
                $output .= htmlBup::text('params[attr][class]',array('value'=>'')).'<br />';
                $output .= langBup::_('id').':<br />';
                $output .= htmlBup::text('params[attr][id]',array('value'=>'')).'<br />';
        }
        return $output;
    }
    /**
     * Generating the list of categories for product extra fields
     * 
     * @param object $field 
     */
    static function productFieldCategories($field){
        if(!empty($field->htmlParams['optionsBup']))
            return;
        /*$field->htmlParams['attrs'] = 'id="select_product_field_cat" rel="0"';
        $field->htmlParams['optionsBup'] = array();
        $categories = frameBup::_()->getModule('products')->getCategories();
        if(!empty($categories)) {
            if(!is_array($field->value['categories']))
                    $field->value['categories'] = array();
            $field->htmlParams['optionsBup'][0] = in_array(0,$field->value['categories'])?langBup::_('Deselect All'):langBup::_('Select All');
            foreach($categories as $c) {
                $field->htmlParams['optionsBup'][$c->term_taxonomy_id] = $c->cat_name;
            }
        }*/
    }
    static public function intToDB($val) {
        return intval($val);
    }
    static public function floatToDB($val) {
        return floatval($val);
    }
	/**
	 * Save this in static var - to futher usage
	 * @return array with countries
	 */
	static public function getCachedCountries($clearCache = false) {
		if(empty(self::$countries) || $clearCache)
			self::$countries = frameBup::_()->getTable('countries')->getAll('id, name, iso_code_2, iso_code_3');
		return self::$countries;
	}
	/**
	 * Save this in static var - to futher usage
	 * @return array with states
	 */
	static public function getCachedStates($clearCache = false) {
		if(empty(self::$states) || $clearCache)
			self::$states = frameBup::_()->getTable('states')
				->leftJoin( frameBup::_()->getTable('countries'), 'country_id' )
				->getAll('toe_states.id,
					toe_states.name, 
					toe_states.code, 
					toe_states.country_id, 
					toe_cry.name AS c_name,
					toe_cry.iso_code_2 AS c_iso_code_2, 
					toe_cry.iso_code_3 AS c_iso_code_3');
		return self::$states;
	}
	static public function getFontsList() {
		return array("Abel", "Abril Fatface", "Aclonica", "Acme", "Actor", "Adamina", "Advent Pro",
			"Aguafina Script", "Aladin", "Aldrich", "Alegreya", "Alegreya SC", "Alex Brush", "Alfa Slab One", "Alice",
			"Alike", "Alike Angular", "Allan", "Allerta", "Allerta Stencil", "Allura", "Almendra", "Almendra SC", "Amaranth",
			"Amatic SC", "Amethysta", "Andada", "Andika", "Angkor", "Annie Use Your Telescope", "Anonymous Pro", "Antic",
			"Antic Didone", "Antic Slab", "Anton", "Arapey", "Arbutus", "Architects Daughter", "Arimo", "Arizonia", "Armata",
			"Artifika", "Arvo", "Asap", "Asset", "Astloch", "Asul", "Atomic Age", "Aubrey", "Audiowide", "Average",
			"Averia Gruesa Libre", "Averia Libre", "Averia Sans Libre", "Averia Serif Libre", "Bad Script", "Balthazar",
			"Bangers", "Basic", "Battambang", "Baumans", "Bayon", "Belgrano", "Belleza", "Bentham", "Berkshire Swash",
			"Bevan", "Bigshot One", "Bilbo", "Bilbo Swash Caps", "Bitter", "Black Ops One", "Bokor", "Bonbon", "Boogaloo",
			"Bowlby One", "Bowlby One SC", "Brawler", "Bree Serif", "Bubblegum Sans", "Buda", "Buenard", "Butcherman",
			"Butterfly Kids", "Cabin", "Cabin Condensed", "Cabin Sketch", "Caesar Dressing", "Cagliostro", "Calligraffitti",
			"Cambo", "Candal", "Cantarell", "Cantata One", "Cardo", "Carme", "Carter One", "Caudex", "Cedarville Cursive",
			"Ceviche One", "Changa One", "Chango", "Chau Philomene One", "Chelsea Market", "Chenla", "Cherry Cream Soda",
			"Chewy", "Chicle", "Chivo", "Coda", "Coda Caption", "Codystar", "Comfortaa", "Coming Soon", "Concert One",
			"Condiment", "Content", "Contrail One", "Convergence", "Cookie", "Copse", "Corben", "Cousine", "Coustard",
			"Covered By Your Grace", "Crafty Girls", "Creepster", "Crete Round", "Crimson Text", "Crushed", "Cuprum", "Cutive",
			"Damion", "Dancing Script", "Dangrek", "Dawning of a New Day", "Days One", "Delius", "Delius Swash Caps", 
			"Delius Unicase", "Della Respira", "Devonshire", "Didact Gothic", "Diplomata", "Diplomata SC", "Doppio One", 
			"Dorsa", "Dosis", "Dr Sugiyama", "Droid Sans", "Droid Sans Mono", "Droid Serif", "Duru Sans", "Dynalight",
			"EB Garamond", "Eater", "Economica", "Electrolize", "Emblema One", "Emilys Candy", "Engagement", "Enriqueta",
			"Erica One", "Esteban", "Euphoria Script", "Ewert", "Exo", "Expletus Sans", "Fanwood Text", "Fascinate", "Fascinate Inline",
			"Federant", "Federo", "Felipa", "Fjord One", "Flamenco", "Flavors", "Fondamento", "Fontdiner Swanky", "Forum",
			"Francois One", "Fredericka the Great", "Fredoka One", "Freehand", "Fresca", "Frijole", "Fugaz One", "GFS Didot",
			"GFS Neohellenic", "Galdeano", "Gentium Basic", "Gentium Book Basic", "Geo", "Geostar", "Geostar Fill", "Germania One",
			"Give You Glory", "Glass Antiqua", "Glegoo", "Gloria Hallelujah", "Goblin One", "Gochi Hand", "Gorditas",
			"Goudy Bookletter 1911", "Graduate", "Gravitas One", "Great Vibes", "Gruppo", "Gudea", "Habibi", "Hammersmith One",
			"Handlee", "Hanuman", "Happy Monkey", "Henny Penny", "Herr Von Muellerhoff", "Holtwood One SC", "Homemade Apple",
			"Homenaje", "IM Fell DW Pica", "IM Fell DW Pica SC", "IM Fell Double Pica", "IM Fell Double Pica SC",
			"IM Fell English", "IM Fell English SC", "IM Fell French Canon", "IM Fell French Canon SC", "IM Fell Great Primer",
			"IM Fell Great Primer SC", "Iceberg", "Iceland", "Imprima", "Inconsolata", "Inder", "Indie Flower", "Inika",
			"Irish Grover", "Istok Web", "Italiana", "Italianno", "Jim Nightshade", "Jockey One", "Jolly Lodger", "Josefin Sans",
			"Josefin Slab", "Judson", "Julee", "Junge", "Jura", "Just Another Hand", "Just Me Again Down Here", "Kameron",
			"Karla", "Kaushan Script", "Kelly Slab", "Kenia", "Khmer", "Knewave", "Kotta One", "Koulen", "Kranky", "Kreon",
			"Kristi", "Krona One", "La Belle Aurore", "Lancelot", "Lato", "League Script", "Leckerli One", "Ledger", "Lekton",
			"Lemon", "Lilita One", "Limelight", "Linden Hill", "Lobster", "Lobster Two", "Londrina Outline", "Londrina Shadow",
			"Londrina Sketch", "Londrina Solid", "Lora", "Love Ya Like A Sister", "Loved by the King", "Lovers Quarrel",
			"Luckiest Guy", "Lusitana", "Lustria", "Macondo", "Macondo Swash Caps", "Magra", "Maiden Orange", "Mako", "Marck Script",
			"Marko One", "Marmelad", "Marvel", "Mate", "Mate SC", "Maven Pro", "Meddon", "MedievalSharp", "Medula One", "Merriweather",
			"Metal", "Metamorphous", "Michroma", "Miltonian", "Miltonian Tattoo", "Miniver", "Miss Fajardose", "Modern Antiqua",
			"Molengo", "Monofett", "Monoton", "Monsieur La Doulaise", "Montaga", "Montez", "Montserrat", "Moul", "Moulpali",
			"Mountains of Christmas", "Mr Bedfort", "Mr Dafoe", "Mr De Haviland", "Mrs Saint Delafield", "Mrs Sheppards",
			"Muli", "Mystery Quest", "Neucha", "Neuton", "News Cycle", "Niconne", "Nixie One", "Nobile", "Nokora", "Norican",
			"Nosifer", "Nothing You Could Do", "Noticia Text", "Nova Cut", "Nova Flat", "Nova Mono", "Nova Oval", "Nova Round",
			"Nova Script", "Nova Slim", "Nova Square", "Numans", "Nunito", "Odor Mean Chey", "Old Standard TT", "Oldenburg",
			"Oleo Script", "Open Sans", "Open Sans Condensed", "Orbitron", "Original Surfer", "Oswald", "Over the Rainbow",
			"Overlock", "Overlock SC", "Ovo", "Oxygen", "PT Mono", "PT Sans", "PT Sans Caption", "PT Sans Narrow", "PT Serif",
			"PT Serif Caption", "Pacifico", "Parisienne", "Passero One", "Passion One", "Patrick Hand", "Patua One", "Paytone One",
			"Permanent Marker", "Petrona", "Philosopher", "Piedra", "Pinyon Script", "Plaster", "Play", "Playball", "Playfair Display",
			"Podkova", "Poiret One", "Poller One", "Poly", "Pompiere", "Pontano Sans", "Port Lligat Sans", "Port Lligat Slab",
			"Prata", "Preahvihear", "Press Start 2P", "Princess Sofia", "Prociono", "Prosto One", "Puritan", "Quantico",
			"Quattrocento", "Quattrocento Sans", "Questrial", "Quicksand", "Qwigley", "Radley", "Raleway", "Rammetto One",
			"Rancho", "Rationale", "Redressed", "Reenie Beanie", "Revalia", "Ribeye", "Ribeye Marrow", "Righteous", "Rochester",
			"Rock Salt", "Rokkitt", "Ropa Sans", "Rosario", "Rosarivo", "Rouge Script", "Ruda", "Ruge Boogie", "Ruluko",
			"Ruslan Display", "Russo One", "Ruthie", "Sail", "Salsa", "Sancreek", "Sansita One", "Sarina", "Satisfy", "Schoolbell",
			"Seaweed Script", "Sevillana", "Shadows Into Light", "Shadows Into Light Two", "Shanti", "Share", "Shojumaru",
			"Short Stack", "Siemreap", "Sigmar One", "Signika", "Signika Negative", "Simonetta", "Sirin Stencil", "Six Caps",
			"Slackey", "Smokum", "Smythe", "Sniglet", "Snippet", "Sofia", "Sonsie One", "Sorts Mill Goudy", "Special Elite",
			"Spicy Rice", "Spinnaker", "Spirax", "Squada One", "Stardos Stencil", "Stint Ultra Condensed", "Stint Ultra Expanded",
			"Stoke", "Sue Ellen Francisco", "Sunshiney", "Supermercado One", "Suwannaphum", "Swanky and Moo Moo", "Syncopate",
			"Tangerine", "Taprom", "Telex", "Tenor Sans", "The Girl Next Door", "Tienne", "Tinos", "Titan One", "Trade Winds",
			"Trocchi", "Trochut", "Trykker", "Tulpen One", "Ubuntu", "Ubuntu Condensed", "Ubuntu Mono", "Ultra", "Uncial Antiqua",
			"UnifrakturCook", "UnifrakturMaguntia", "Unkempt", "Unlock", "Unna", "VT323", "Varela", "Varela Round", "Vast Shadow",
			"Vibur", "Vidaloka", "Viga", "Voces", "Volkhov", "Vollkorn", "Voltaire", "Waiting for the Sunrise", "Wallpoet",
			"Walter Turncoat", "Wellfleet", "Wire One", "Yanone Kaffeesatz", "Yellowtail", "Yeseva One", "Yesteryear", "Zeyada"
		);
	}
}
?>
