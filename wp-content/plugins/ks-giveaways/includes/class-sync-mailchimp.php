<?php

if (!class_exists('KS_Giveaways_Vendor_Mailchimp')) {
    require_once dirname(__FILE__) . '/vendor/mailchimp_api/Mailchimp.php';
}

class KS_Giveaways_Mailchimp
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;

    protected $mailchimp = null;

    private function __construct()
    {
        $this->key = get_option(KS_GIVEAWAYS_OPTION_MAILCHIMP_KEY);

        try {
            $this->mailchimp = new KS_Giveaways_Vendor_Mailchimp($this->key);
        }
        catch(Exception $e) {
            $this->mailchimp = null;
        }
    }

    /**
     * Returns an instance of this class.
     *
     * @return  object    A single instance of this class.
     */
    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get_lists()
    {
        /*
         * MailChimp getList() methods are limited to 100 max
         * So we will need to call it repeatedly until we run out
         *
         * This may change with v3 API
         */
        try {
            if (!$this->mailchimp)
                throw new Exception('Mailchimp not connected');

            $lists = array();

            /** @var KS_Giveaways_Vendor_Mailchimp_Lists $mailchimp_lists_api */
            /** @noinspection PhpUndefinedFieldInspection */
            $mailchimp_lists_api = $this->mailchimp->lists;

            $start = 0; // Note: Start is pages, not the actual list number. So after each iteration, we add 1
            $limit = 100;

            while(true)
            {
                // NOTE: The API returns an array of 0 if page is empty - not an error
                $ret = $mailchimp_lists_api->getList(array(), $start, $limit);

                if (isset($ret['data']))
                {
                    foreach ($ret['data'] as $list)
                    {
                        $lists[$list['id']] = $list['name'];
                    }

                    if (count($ret['data']) < $limit)
                    {
                        break;
                    }

                    $start += 1;
                }
            }

            return $lists;
        }

        catch(Exception $e) {
        }

        return false;
    }

    public function add_subscriber($list_id, $email, $first_name = null)
    {
        try {
            if (!$this->mailchimp)
                throw new Exception('Mailchimp not connected');

            if ($first_name) {
                $first_name = array('FNAME' => $first_name);
            }

            $ret = $this->mailchimp->lists->subscribe($list_id, array('email' => $email), $first_name, 'html', false);

            return true;
        }

        catch(KS_Giveaways_Vendor_Mailchimp_List_AlreadySubscribed $e) {
            return true;
        }

        catch(Exception $e) {
            return $e->getMessage();
        }

        return false;
    }

    public function ping()
    {
        try {
            $this->mailchimp->helper->ping();

            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }

    public static function disconnect()
    {
        delete_option(KS_GIVEAWAYS_OPTION_MAILCHIMP_KEY);
    }

    public static function is_valid()
    {
        if (get_option(KS_GIVEAWAYS_OPTION_MAILCHIMP_KEY)) {
            $cls = self::get_instance();

            return $cls->ping();
        }

        return false;
    }
}