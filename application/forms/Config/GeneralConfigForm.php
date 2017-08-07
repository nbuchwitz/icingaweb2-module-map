<?php

namespace Icinga\Module\Map\Forms\Config;

use Icinga\Application\Config;
use Icinga\Forms\ConfigForm;

class GeneralConfigForm extends ConfigForm
{
    /**
     * Initialize this form
     */
    public function init()
    {
        $this->setName('form_config_map_general');
        $this->setSubmitLabel('Save Changes');
    }

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $this->addElement(
            'text',
            'map_default_lat',
            array(
                'placeholder' => '52.520645',
                'label' => $this->translate('Default latitude (WGS84)'),
                'description' => $this->translate('Default map position (latitude)'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_default_long',
            array(
                'placeholder' => '13.409779',
                'label' => $this->translate('Default longitude (WGS84)'),
                'description' => $this->translate('Default map position (longitude)'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_default_zoom',
            array(
                'placeholder' => '6',
                'label' => $this->translate('Default zoom level'),
                'description' => $this->translate('Default zoom level of the map'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_max_zoom',
            array(
                'placeholder' => '19',
                'label' => $this->translate('Maximum zoom level'),
                'description' => $this->translate('Maximum zoom level of the map'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_min_zoom',
            array(
                'placeholder' => '5',
                'label' => $this->translate('Minimal zoom level'),
                'description' => $this->translate('Minimal zoom level of the map'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_dashlet_height',
            array(
                'placeholder' => '300',
                'label' => $this->translate('Dashlet height'),
                'description' => $this->translate('Dashlet height'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_marker_size',
            array(
                'placeholder' => '15',
                'label' => $this->translate('Marker size'),
                'description' => $this->translate('Size of the host markers'),
                'required' => false
            )
        );
        $this->addElement(
            'select',
            'map_stateType',
            array(
                'label' => $this->translate('State type'),
                'description' => $this->translate('State type for status indication'),
                'multiOptions' => array(
                    'soft' => 'soft',
                    'hard' => 'hard'
                ),
            )
        );
        
    }
}

