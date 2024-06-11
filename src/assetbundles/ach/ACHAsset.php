<?php

namespace webdna\commerce\braintree\assetbundles\ach;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    David Casini
 * @package   CommerceBraintree
 * @since     1.0.0
 */
class ACHAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
		$this->sourcePath = __DIR__.'/dist';

        $this->depends = [
            //CpAsset::class,
        ];

        $this->js = [
            'js/ach-braintree.js',
        ];

        /*
        $this->css = [
            'css/DropinUi.css',
        ];
        */

        parent::init();
    }
}
