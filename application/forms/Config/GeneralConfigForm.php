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
        $this->setSubmitLabel($this->translate('Save Changes'));
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
            'map_max_native_zoom',
            array(
                'placeholder' => '19',
                'label' => $this->translate('Maximum native zoom level '),
                'description' => $this->translate('Maximum zoom level natively supported by the map'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_min_zoom',
            array(
                'placeholder' => '2',
                'label' => $this->translate('Minimal zoom level'),
                'description' => $this->translate('Minimal zoom level of the map'),
                'required' => false
            )
        );
        $this->addElement(
            'text',
            'map_tile_url',
            array(
                'placeholder' => '//\{s\}.tile.openstreetmap.org/\{z\}/\{x\}/\{y\}.png',
                'label' => $this->translate('URL for tile server'),
                'description' => $this->translate('Escaped server url, for leaflet tilelayer'),
                'required' => false,
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
        $this->addElement(
            'text',
            'map_disable_cluster_at_zoom',
            array(
                'label' => $this->translate('Disable clustering at zoomlevel'),
                'description' => $this->translate('Don\'t cluster marker at a certain zoomlevel. Use 1 for disabling clustering'),
                'required' => false,
            )
        );

        $this->addElement(
            'checkbox',
            'map_cluster_problem_count',
            array(
                'label' => $this->translate('Show number of problems in cluster'),
                'description' => $this->translate('Show number of problems in cluster instead of the number of markers'),
                'required' => false,
                'default' => false,
            )
        );

    }
}

