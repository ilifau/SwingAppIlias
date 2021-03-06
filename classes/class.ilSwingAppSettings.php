<?php
// Copyright (c) 2018 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3, see LICENSE

require_once(__DIR__ . '/class.ilSwingAppBaseData.php');

/**
 * SwingApp plugin settings class
 *
 * @author Fred Neumann <fred.neumann@ili.fau.de>
 *
 */
class ilSwingAppSettings extends ilSwingAppBaseData
{

    /** @var int obj_id */
    protected $obj_id;


	/**
	 * Constructor.
     * @param int obj_id;
	 */
	public function __construct($a_obj_id)
	{
        $this->obj_id = $a_obj_id;
        parent::__construct();
	}

    /**
     * Initialize the list of Params
     */
    protected function initParams()
    {
        $this->addParam( ilSwingAppParam::_create(
            'publish_settings',
            $this->plugin->txt('publish_settings'),
            '',
            ilSwingAppParam::TYPE_HEAD,
            null
        ));

        $this->addParam( ilSwingAppParam::_create(
            'publish_dir',
            $this->plugin->txt('publish_dir'),
            $this->plugin->txt('publish_dir_info'),
            ilSwingAppParam::TYPE_TEXT,
            ''
        ));

        $this->addParam( ilSwingAppParam::_create(
            'publish_url',
            $this->plugin->txt('publish_url'),
            $this->plugin->txt('publish_url_info'),
            ilSwingAppParam::TYPE_TEXT,
            ''
        ));
    }

    /**
     * Read the configuration from the database
     */
	public function read()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "SELECT * FROM swingapp_settings WHERE obj_id = ". $ilDB->quote($this->obj_id, 'integer');
        $res = $ilDB->query($query);
        while($row = $ilDB->fetchAssoc($res))
        {
            $this->set($row['param_name'], $row['param_value']);
        }
    }

    /**
     * Write the configuration to the database
     */
    public function write()
    {
        global $DIC;
        $ilDB = $DIC->database();

        foreach ($this->getParams() as $param)
        {
            $ilDB->replace('swingapp_settings',
                array(
                    'obj_id' =>  array('integer', $this->obj_id),
                    'param_name' => array('text', $param->name)
                ),
                array('param_value' => array('text', (string) $param->value))
            );
        }
    }

    /**
     * Get a list of object titles which can be published
     * @return array id => title
     */
    public static function getPublishableObjects()
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "
            SELECT s.obj_id, s.param_value, o.title
            FROM swingapp_settings s
            INNER JOIN object_data o ON o.obj_id = s.obj_id
            WHERE s.param_name = 'publish_dir'
            AND s.param_value IS NOT NULL AND s.param_value <> ''";

        $result = $ilDB->query($query);

        $objects = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            if (is_dir($row['param_value']) &&
                is_writeable($row['param_value'])) {
                $objects[$row['obj_id']] = $row['title'];
            }
        }

        return $objects;
    }
}