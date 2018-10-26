<?php
// Update the category registry
$result = \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
    'clubdirectory',
    'tx_clubdirectory_domain_model_address'
);
