<?php
/**
 * Ajax "download" route, for outputting raw gettext file contents.
 */
class Loco_ajax_DownloadController extends Loco_mvc_AjaxController {

    /**
     * {@inheritdoc}
     */
    public function render(){

        $post = $this->validate();

        // we need a path, but it may not need to exist
        $file = new Loco_fs_File( $this->get('path') );
        $file->normalize( loco_constant( 'WP_CONTENT_DIR') );

        // Restrict download to gettext file formats
        $ext = Loco_gettext_Data::ext($file);

        // posted source must be clean and must parse as whatever the file extension claims to be
        if( $raw = $post->source ){
            // compile source if target is MO
            if( 'mo' === $ext ) {
                $raw = Loco_gettext_Data::fromSource($raw)->msgfmt();
            }
        }
        // else file can be output directly if it exists.
        // note that files on disk will not be parsed or manipulated. they will download strictly as-is
        else if( $file->exists() ){
            $raw = $file->getContents();
        }
        // else we can't do anything except bail
        else {
            throw new Loco_error_Exception('File not found and no source posted');
        }

        // Observe UTF-8 BOM setting for PO and POT only
        if( 'po' === $ext || 'pot' === $ext ){
            $has_bom = "\xEF\xBB\xBF" === substr($raw,0,3);
            $use_bom = (bool) Loco_data_Settings::get()->po_utf8_bom;
            // only alter file if valid UTF-8. Deferring detection overhead until required 
            if( $has_bom !== $use_bom && preg_match('//u',$raw) ){
                if( $use_bom ){
                    $raw = "\xEF\xBB\xBF".$raw; // prepend
                }
                else {
                    $raw = substr($raw,3); // strip bom
                }
            }
        }

        return $raw;
    }

}
