<?php

class Loco_error_AdminNotices extends Loco_hooks_Hookable {
    
    /**
     * @var Loco_error_AdminNotices
     */
    private static $singleton;

    /**
     * @var Loco_error_Exception[]
     */
    private $errors = array();

    /**
     * Inline messages are handled by our own template views
     * @var bool
     */
    private $inline = false;


    /**
     * @return Loco_error_AdminNotices
     */
    public static function get(){
        self::$singleton or self::$singleton = new Loco_error_AdminNotices;
        return self::$singleton;
    }

    
    /**
     * @param Loco_error_Exception
     * @return Loco_error_Exception
     */
    public static function add( Loco_error_Exception $error ){
        $notices = self::get();
        // if exception wasn't thrown we have to do some work to establish where it was invoked
        if( __FILE__ === $error->getRealFile() ){
            $error->setCallee(1);
        }
        // write error immediately under WP_CLI
        if( 'cli' === PHP_SAPI && class_exists('WP_CLI',false) ){
            $error->logCli();
            return $error;
        }
        // else buffer notices for displaying when UI is ready
        $notices->errors[] = $error;
        // do late flush if we missed the boat
        if( did_action('loco_admin_init') ){
            $notices->on_loco_admin_init();
        }
        if( did_action('admin_notices') ){
            $notices->on_admin_notices();
        }
        // Log message automatically if enabled
        if( $error->loggable() ){
            $error->log();
        }
        return $error;
    }


    /**
     * Raise a success message
     * @param string
     * @return Loco_error_Exception
     */
    public static function success( $message ){
        $notice = new Loco_error_Success($message);
        return self::add( $notice->setCallee(1) );
    }


    /**
     * Raise a failure message
     * @param string
     * @return Loco_error_Exception
     */
    public static function err( $message ){
        $notice = new Loco_error_Exception($message);
        return self::add( $notice->setCallee(1) );
    }


    /**
     * Raise a warning message
     * @param string
     * @return Loco_error_Exception
     */
    public static function warn( $message ){
        $notice = new Loco_error_Warning($message);
        return self::add( $notice->setCallee(1) );
    }


    /**
     * Raise a generic info message
     * @param string
     * @return Loco_error_Exception
     */
    public static function info( $message ){
        $notice = new Loco_error_Notice($message);
        return self::add( $notice->setCallee(1) );
    }


    /**
     * Raise a debug notice, if debug is enabled
     * @param string
     * @return Loco_error_Debug
     */
    public static function debug( $message ){
        $notice = new Loco_error_Debug($message);
        $notice->setCallee(1);
        loco_debugging() and self::add( $notice );
        return $notice;
    }


    /**
     * Destroy and return buffer
     * @return Loco_error_Exception[]
     */
    public static function destroy(){
        $notices = self::$singleton;
        if( $notices instanceof  Loco_error_AdminNotices ){
            $buffer = $notices->errors;
            $notices->errors = array();
            self::$singleton = null;
            return $buffer;
        }
        return array();
    }



    /**
     * Destroy and return all serialized notices, suitable for ajax response 
     * @return array
     */
    public static function destroyAjax(){
        $data = array();
        foreach( self::destroy() as $notice ){
            $data[] = $notice->jsonSerialize();
        }
        return $data;
    }

    
    /**
     * @return void
     */
    private function flushHtml(){
        if( $this->errors ){
            $htmls = array();
            foreach( $this->errors as $error ){
                $html = sprintf (
                    '<p><strong class="has-icon">%s:</strong> <span>%s</span></p>',
                    esc_html( $error->getTitle() ),
                    esc_html( $error->getMessage() )
                );
                $styles = array( 'notice', 'notice-'.$error->getType() );
                if( $this->inline ){
                    $styles[] = 'inline';
                }
                if( $links = $error->getLinks() ){
                    $styles[] = 'has-nav';
                    $html .= '<nav>'.implode( '<span> | </span>', $links ).'</nav>';
                }
                $htmls[] = '<div class="'.implode(' ',$styles).'">'.$html.'</div>';
            }
            $this->errors = array();
            echo implode("\n", $htmls),"\n";
        }
    }

    /**
     * @return void
     */
    private function flushCli(){
        foreach( $this->errors as $e ){
            $e->logCli();
        }
        $this->errors = array();
        
    }


    /**
     * admin_notices action handler.
     */
    public function on_admin_notices(){
        if( ! $this->inline ){
            $this->flushHtml();
        }
    }


    /**
     * loco_admin_notices callback.
     * Unlike WordPress "admin_notices" this fires from within template layout at the point we want them, hence they are marked as "inline"
     */
    public function on_loco_admin_notices(){
        $this->inline = true;
        $this->flushHtml();
    }


    /**
     * loco_admin_init callback
     * When we know a Loco admin controller will render the page we will control the point at which notices are printed
     */
    public function on_loco_admin_init(){
        $this->inline = true;
    }


    /**
     * @internal
     * Make sure we always see notices if hooks didn't fire
     */
    public function __destruct(){
        $this->inline = false;
        $this->flush();
    }


    /**
     * @internal
     */
    public function flush(){
        if( class_exists('WP_CLI',false) ){
            $this->flushCli();
        }
        else if( ! loco_doing_ajax() ){
            $this->flushHtml();
        }
        else {
            $this->errors = array();
        }   
    }

}
