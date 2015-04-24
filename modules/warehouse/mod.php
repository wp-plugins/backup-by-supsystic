<?php

class warehouseBup extends moduleBup
{

    /**
     * @var warehouseModelBup
     */
    private $folder;

    /**
     * @var temporaryModelBup
     */
    private $temporary;

    /**
     * @var bool
     */
    private $warehouseStatus;

    public function init()
    {
        parent::init();

        $this->warehouseStatus = false;

        if (!$this->getFolder()->exists()) {
            if (!$this->getFolder()->create()) {
                add_filter('bup_cant_create_path', array($this, 'getPath'));
                add_action('admin_notices', array($this, '_noticeCantCreate'));

                return;
            }
        }

        if (!$this->getFolder()->isWritable()) {
            add_filter('bup_not_writable_path', array($this, 'getPath'));
            add_action('admin_notices', array($this, '_noticeNotWritable'));

            return;
        }

        if (!$this->getTemp()->exists()) {
            if (!$this->getTemp()->create()) {
                add_filter('bup_cant_create_path', array($this, 'getTemporaryPath'));
                add_action('admin_notices', array($this, '_noticeCantCreate'));

                return;
            }
        }

        if (!$this->getTemp()->isWritable()) {
            add_filter('bup_not_writable_path', array($this, 'getTemporaryPath'));
            add_action('admin_notices', array($this, '_noticeNotWritable'));

            return;
        }

        $this->warehouseStatus = true;
    }

    public function getWarehouseStatus()
    {
        return $this->warehouseStatus;
    }


    public function _noticeNotWritable()
    {
        $path = apply_filters('bup_not_writable_path', null);

        $message = sprintf(
            __('Folder "%s" is not writable.', BUP_LANG_CODE),
            $path
        );

        echo $this->getNotice($message);
    }

    public function _noticeCantCreate()
    {
        $path = apply_filters('bup_cant_create_path', null);

        $message = sprintf(
            __('Can\'t create warehouse directory "%s".', BUP_LANG_CODE),
            $path
        );

        echo $this->getNotice($message);
    }

    /**
     * Returns folder model.
     * @return warehouseModelBup
     */
    public function getFolder()
    {
        if (!$this->folder) {
            $this->folder = $this->getModel('warehouse');
        }

        return $this->folder;
    }

    /**
     * Returns temporary folder model.
     * @return temporaryModelBup
     */
    public function getTemp()
    {
        if (!$this->temporary) {
            $basePath = $this->getFolder()->getPath();

            $this->temporary = $this->getModel('temporary');
            $this->temporary->setBasePath($basePath);
        }

        return $this->temporary;
    }

    public function getPath()
    {
        return $this->getFolder()->getPath();
    }

    public function getTemporaryPath()
    {
        return $this->getTemp()->getPath();
    }

    protected function getNotice($message)
    {
        return sprintf(
            '<div class="error"><p><strong>Backup by Supsystic</strong><br/>%s</p></div>',
            $message
        );
    }
}
