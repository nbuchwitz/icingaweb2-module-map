<?php

namespace Icinga\Module\Map\Forms\Config;

use Icinga\Forms\ConfigForm;

/**
 * DirectorConfigForm is the configuration form used for Director specific configuration
 */
class DirectorConfigForm extends ConfigForm
{
    /**
     * Initialize this form
     */
    public function init()
    {
        $this->setName('form_config_director');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $this->addElement(
            'text',
            'director_default_lat',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Default latitude (WGS84)'),
                'description' => $this->translate('Default map position (latitude)'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_default_long',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Default longitude (WGS84)'),
                'description' => $this->translate('Default map position (longitude)'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_default_zoom',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Default zoom level'),
                'description' => $this->translate('Default zoom level of the map'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_max_zoom',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Maximum zoom level'),
                'description' => $this->translate('Maximum zoom level of the map'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_max_native_zoom',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Maximum native zoom level '),
                'description' => $this->translate('Maximum zoom level natively supported by the map'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_min_zoom',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('Minimal zoom level'),
                'description' => $this->translate('Minimal zoom level of the map'),
                'required' => false
            ]
        );
        $this->addElement(
            'text',
            'director_tile_url',
            [
                'placeholder' => '(use map modules configuration)',
                'label' => $this->translate('URL for tile server'),
                'description' => $this->translate('Escaped server url, for leaflet tilelayer'),
                'required' => false,
            ]
        );
    }
}
