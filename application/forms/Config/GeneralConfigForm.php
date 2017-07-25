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
                'label' => $this->translate('Latitude'),
                'description' => $this->translate('Default map position (latitude)'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_default_long',
            array(
                'placeholder' => '13.409779',
                'label' => $this->translate('Longitude'),
                'description' => $this->translate('Default map position (longitude)'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_default_zoom',
            array(
                'placeholder' => '6',
                'label' => $this->translate('Default Zoom'),
                'description' => $this->translate('Default zoom'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_max_zoom',
            array(
                'placeholder' => '19',
                'label' => $this->translate('Max Zoom'),
                'description' => $this->translate('Max zoom'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_min_zoom',
            array(
                'placeholder' => '5',
                'label' => $this->translate('Min Zoom'),
                'description' => $this->translate('Min zoom'),
                'required' => false
            )
        );
        
    }
}

