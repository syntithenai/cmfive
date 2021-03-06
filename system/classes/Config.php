<?php

/**
 * This class is responsible for storing and accessing 
 * the configurations for each class with case insensitive keys. 
 *
 * Config getting and setting is done using the dot syntax i.e.
 *     Config::set("admin.topmenu", true);
 * Translates to:
 *     $register["admin"]["topmenu"] = true;
 * Then the inverse function also works:
 *     Config::get("admin.topmenu"); // returns true
 * 
 * If any keys aren't found in set then they are created, also the value
 * itself can be an array, of which the values within can then be subsequently
 * retrieved with the abovementioned get function and dot syntax.
 * 
 * Note: When calling Config::get, if a key is not found NULL is returned so it 
 * is important to check that condition when fetching config keys.
 * 
 * @author Adam Buckley
 */

class Config {
    
    // Storage array
    private static $register = array();
    
    /**
     * This function will set a key in an array
     * to the value given
     *
     * @param string $key
     * @param mixed $value
     * @return null
     */
    public static function set($key, $value) {
        $exploded_key = explode('.', $key);
        if (!empty($exploded_key)) {
            $register = &self::$register;
            // Loop through each key
            foreach($exploded_key as $ekey) {
                $i_ekey = strtolower($ekey);
                if (!array_key_exists($i_ekey, $register)) {
                    $register[$i_ekey] = array();
                }
                $register = &$register[$i_ekey];
            }
            $register = $value;
        }
    }
    
    /**
     * This function will attempt to return a
     * key out of the array
     *
     * @param string $key
     * @return Mixed the value
     */
    public static function get($key) {
        $exploded_key = explode('.', $key);
        // Copy the register for processing
        $value = self::$register;
        if (!empty($exploded_key)) {
            // Loop through each key
            foreach($exploded_key as $ekey) {
                if (array_key_exists(strtolower($ekey), $value)) {
                    $value = $value[strtolower($ekey)];
                } else {
                    // Return null when we can't find a key
                    return NULL;
                }
            }
            return $value;
        }
        return NULL;
    }
    
    /**
     * A small helper function for web to get the list of keys (modules)
     * 
     * @return array
     */
    public static function keys($getAll = false) {
        if ($getAll === true) {
            return array_keys(self::$register);
        }
        $required = array("topmenu", "active", "path");
        $req_count = count($required);
        $modules = array_filter(self::$register, function($var) use ($required, $req_count) {
            return ($req_count === count(array_intersect_key($var, array_flip($required))));
        });

        return array_keys($modules);
    }
    
    // Sanity checking
    public static function dump() {
        var_dump(self::$register);
    }
}
    