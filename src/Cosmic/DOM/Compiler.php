


    /**
     * Convert the given input string from camelCase to dashed-case.
     * 
     * @param array $attributes The input string to convert.
     * 
     * @return array The string converted to dashed case.
     */
    public static function parseMultivalueFields(array $attributes): array
    {
        foreach ($attributes as $attribute => $value) {

            $multivalue = explode("&", $attribute);

            if (count($multivalue) > 1) {

                unset($attributes[$attribute]);
                
                foreach ($multivalue as $multi) {
                    $attributes[$multi] = $value;
                }
            }
        }

        ksort($attributes);
        return $attributes;
    }
