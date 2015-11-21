<?php

namespace Core\Controllers;

class ControllerUtils {
    
    /**
     * splitCamelCase splits up camelCase words for readability
     * @param type $input
     * @return type
     */
    public function splitCamelCase($input)
    {
        return preg_split(
            '/(^[^A-Z]+|[A-Z][^A-Z]+)/',
            $input,
            -1, /* no limit for replacement count */
            PREG_SPLIT_NO_EMPTY /*don't return empty elements*/
                | PREG_SPLIT_DELIM_CAPTURE /*don't strip anything from output array*/
        );
    }
    
    /**
     * 
     * @param array $input
     * @param array $merge
     * @return null
     */
    public function mergeArrays(array $input, array $merge)
    {
        
        return null;
    }
    
}
