<?php

class PP_Capabilities_Notices
{

    /**
     * Notification name to use
     * 
     */
    protected $notification = 'pp_roles_notification';

    /**
     * All types of notifications allowed
     *
     * @var string[]
     */
    protected $types = array('error', 'success', 'warning', 'info');

    /**
     * All current messages
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Pp_Roles_Notifications constructor.
     *
     */
    public function __construct()
    {
        /**
         * Read notification if exist
         */
        if (get_option($this->notification)) {
            $messages = get_option($this->notification);
            $messages = @json_decode($messages, true);
            if (is_array($messages)) {
                $this->messages = [];
                foreach($messages as $message_type => $message_content){
                    $this->messages[$message_type] = array_map('esc_html', $message_content);
                }
            }
        }
    }

    /**
     * Display all messages
     *
     */
    public function display()
    {
        $html = '';
        foreach ($this->types as $type) {
            $messages = $this->get($type);
            foreach ($messages as $message) {
                if (is_string($message)) {
                    printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr($type), esc_html($message));
                }
            }
        }

        /**
         * Delete the notification after display
         */
        delete_option($this->notification);
    }

    /**
     * Get all messages for a given type
     *
     * @param string $type
     *
     * @return array The messages
     */
    protected function get($type)
    {
        $messages = array();
        if (isset($this->messages[$type]) && is_array($this->messages[$type])) {
            $messages = $this->messages[$type];
        }

        return $messages;
    }

    /**
     * @param string $type The type of notification to show to the user
     *                     [error|success|warning|info]
     * @param string $msg The message to show to the user
     *
     * @return bool If notification was added successfully
     */
    public function add($type, $msg)
    {
        if (!in_array($type, $this->types) || !is_string($msg)) {
            return false;
        }

        $messages = $this->get($type);
        $messages[] = $msg;

        //Update the messages
        $this->messages[$type] = $messages;

        update_option($this->notification, json_encode($this->messages));

        return true;
    }

    /**
     * Show an error message
     *
     * @param string $msg
     *
     */
    public function error($msg)
    {
        $this->add('error', $msg);
    }

    /**
     * Show a success message
     *
     * @param string $msg
     *
     */
    public function success($msg)
    {
        $this->add('success', $msg);
    }

    /**
     * Show a warning message
     *
     * @param string $msg
     *
     */
    public function warning($msg)
    {
        $this->add('warning', $msg);
    }

    /**
     * Show an info message
     *
     * @param string $msg
     *
     */
    public function info($msg)
    {
        $this->add('info', $msg);
    }
}
