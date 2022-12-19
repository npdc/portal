<?php

/**
 * Function to push new content to external sources
 */

namespace npdc\lib;

class Push {
    /**
     * Send content to service
     *
     * @param string $title short message
     * @param string $url url of content
     * @param string|null $text long message text (if any)
     * @return void
     */
    public static function send($title, $url, $text=null) {
        if (!NPDC_DEV && !empty(\npdc\config::$ifttt['token'])) {
            $events = is_array(\npdc\config::$ifttt['event']) 
                ? \npdc\config::$ifttt['event']
                : [\npdc\config::$ifttt['event']];
            foreach($events as $event){
                $curl = 'https://maker.ifttt.com/trigger/'
                    . $event
                    . '/with/key/'
                    . \npdc\config::$ifttt['token'];
                $ch = curl_init($curl);
                $xml = 'value1='
                    . urlencode(
                        html_entity_decode(
                            filter_var($title, FILTER_SANITIZE_STRING)
                        )
                    )
                    . '&value2='.$url;
                if (!empty($text)) {
                    $xml .= '&value3=' 
                        . urlencode(
                            html_entity_decode(
                                filter_var($text, FILTER_SANITIZE_STRING)
                            )
                        );
                }

                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

                curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}