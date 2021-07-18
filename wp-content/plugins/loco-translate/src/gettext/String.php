<?php
/**
 * Gettext source string. Does not contain translations.
 */
class Loco_gettext_String {

    /**
     * @var array
     */
    private $raw;

    /**
     * @var string
     */
    private $plural;
    

    /**
     * Loco_gettext_String constructor.
     *
     * @param string Mandatory source
     * @param string Optional context
     */
    public function __construct( $msgid, $msgctxt = '' ){
        $this->raw = array (
            'source' => (string) $msgid,
            'context' => (string) $msgctxt,
        );
    }


    /**
     * Get singular form as raw array data
     * @internal
     * @return string[]
     */
    public function exportSingular(){
        return $this->raw;
    }


    /**
     * Get plural form as raw array data
     * @internal
     * @return string[]
     */
    public function exportPlural(){
        return array (
            'source' => $this->plural,
        );
    }


    /**
     * @param string
     * @param string|array
     * @param string
     * @return void
     */
    private function merge( $prop, $value, $glue ){
        if( is_string($value) ){
            $value = array($value);
        }
        else if( ! is_array($value) ){
            throw new InvalidArgumentException('Expected Array or String');
        }
        if( array_key_exists($prop,$this->raw) ){
            $value = array_merge( explode($glue,$this->raw[$prop]), $value );
        }
        $this->raw[$prop] = implode($glue,$value);
    }


    /**
     * @param array|string
     * @return self
     */
    public function addFileReferences( $refs ){
        $this->merge('refs',$refs,' ');
        return $this;
    }


    /**
     * @param array|string
     * @return self
     */
    public function addExtractedComment( $notes ){
        $this->merge('notes',$notes,' ');
        return $this;
    }

    /**
     * @param string
     * @return self
     */
    public function pluralize( $msgid_plural ){
        $this->plural = (string) $msgid_plural;
        return $this;
    }


    /**
     * @return bool
     */
    public function hasPlural(){
        return is_string($this->plural) && '' !== $this->plural;
    }

}