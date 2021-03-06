<?php
/**
 * JsonValidate is part of Wallace Point of Sale system (WPOS) API
 *
 * JsonValidate is used to validate JSON data using a provided JSON schema. Each property in the schema specifies a rule that the corresponding data property must pass.
 *
 * WallacePOS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * WallacePOS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)
 * @link       https://wallacepos.com
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      File available since 17/05/14 4:07 PM
 */
class JsonValidate {
    /**
     * @var mixed Json object providing data to be validated
     */
    var $data;
    /**
     * @var mixed Json object providing schema rules
     */
    var $schema;

    /**
     * @param $data
     * @param $schema
     * @return $this|bool Returns false on failure to decode the schema, or the initialized object on success.
     */
    function JsonValidate($data, $schema){
        if ($data===null || $schema===null){
            // return false if schema or data == null
            return false;
        }
        $this->schema = json_decode($schema, true);
        $this->data = $data;
        if ($this->schema ===false){
            // Json decoding failure
            return false;
        }
        return $this;
    }

    /**
     * @return bool|string Returns true on success or a string of errors on failure.
     */
    public function validate(){
        // loop through each schema value and check if data exists
        $errors = "";
        foreach ($this->schema as $key => $value){
            // if specified in schema, the data key must be set and not "" (empty)
            if (!isset($this->data->$key)){
                // key not specified
                $errors.=$key." must be specified\n";
            } else {
                // check if rule is set for the value, if not, just check if it's not blank
                if ($value == ""){
                    // "" check
                    if ($this->data->$key == ""){
                        $errors.=$key." must not be blank\n";
                    }
                } else {
                    // validate via rule
                    $valid = $this->validateValue($this->data->$key, $value);
                    if ($valid!==true){
                        $errors.=$key." ".$valid;
                    }
                }
            }
        }
        // return true if no errors found
        if ($errors == ""){
            return true;
        }
        return $errors;
    }

    /**
     * Finds the specified schema rule and matches the value
     * @param $dataval
     * @param $schemaval
     * @return bool|string Returns true if the value is valid or an error string on failure
     */
    private function validateValue($dataval, $schemaval){
        if (strlen($schemaval)==1){
            // one character rules
            switch ($schemaval){
                case "~": // can be any value
                    return true;
                    break;
                case 1: // must be a number or float
                    if (!is_numeric($dataval)){
                        return "must be numeric\n";
                    }
                    break;
                case "@": // must be a valid email
                    if (strpos($dataval, "@")===false){
                        return "must be a valid email address\n";
                    }
                    break;
                case "[": // must be an array with at least 1 value
                    if (!is_array($dataval) || !sizeof($dataval)>0){
                        return "must be an array with at least one value\n";
                    }
                    break;
                default:
                    break;
            }
        } else {
            // multi character rules
            $rule = substr($schemaval, 0, 2);
            $val = substr($schemaval, 2, strlen($schemaval));
            switch ($rule){
                case -1: // must be numeric but can be null
                    if ($dataval!=="" && !is_numeric($dataval)){
                        return "must be numeric\n";
                    }
                    break;
                case "!=": // must not equals
                    if ($dataval == $val){
                        return "must not equal ".$val."\n";
                    }
                    break;
                case "<=": // must be no larger than
                    if ($dataval > $val){
                        return "must be no larger than ".$val."\n";
                    }
                    break;
                case ">=": // must be equal or larger than
                    if ($dataval < $val){
                        return "must be larger than ".$val."\n";
                    }
                    break;
            }
        }
        return true;
    }

}