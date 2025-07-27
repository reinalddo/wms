<?php
/**
 * This file contains trait to bind visual queries' params
 *
 * @author KoolPHP Inc (support@koolphp.net)
 * @link https://www.koolphp.net
 * @copyright KoolPHP Inc
 * @license https://www.koolreport.com/license#mit-license
 */


namespace koolreport\visualquery;

use \koolreport\core\Utility as Util;

trait Bindable
{
    public $queryParams;
    public $vqHelper;

    public function __constructVisualQueryBindable()
    {
        $this->vqHelper = new VisualQueryHelper();
        $this->registerEvent("OnInit", function(){
            $this->queryParams = $this->vqHelper->inputToParams();
        });
    }

    public function paramsToValue($params)
    {
        $value = $this->vqHelper->paramsToValue($params);
        return $value;
    }

    public function valueToQueryBuilder($schemas, $params)
    {
        $queryBuilder = $this->vqHelper->valueToQueryBuilder($schemas, $params);
        return $queryBuilder;
    }

    public function paramsToQueryBuilder($params)
    {
        if (method_exists($this, 'defineSchemas')) {
            $schemas =  $this->defineSchemas();
        } else {
            $schemas = [];
            // $schema = json_decode(Util::get($params, 'schema', '[]'), true);
            // $schemas = ["schema_0" => $schema];
        }
        $value = $this->vqHelper->paramsToValue($params);
        $queryBuilder =  $this->vqHelper->valueToQueryBuilder($schemas, $value);
        return $queryBuilder;
    }

}