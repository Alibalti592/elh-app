<?php
namespace App\Services;

class UnitsService {
   CONST YEAR_IN_SECONDS = 31536000;
   CONST MONTH_IN_SECONDS = 2592000;

    //
    // ***************** TIME FUNCTIONS ************
    //

    public function setReadableTimeFromMinutes($minutes, $forInput = false){
        $hours = floor($minutes / 60);
        $minutes = $minutes  - ($hours * 60);
        if($minutes < 10 and $minutes > 0) {
            $minutes = "0$minutes";
        }
        if($forInput) {
            if($hours<10) {
                $hours = "0$hours";
            }
            if($minutes == 0) {
                $minutes = "00";
            }
            return $hours.":".$minutes;
        }
        if($minutes == 0) {
            $minutes = "00";
        }
        return $hours."h".$minutes;
    }

    public function setReadableTimeFromSeconds($seconds, $showSeconds = true) {
        if($seconds == 0) {
            return "";
        }
        $hours = floor($seconds / 3600);
        $leftSeconds = $seconds  - ($hours * 3600);
        $minutes = floor($leftSeconds / 60);
        $leftSeconds = $leftSeconds  - ($minutes * 60);
        $readableHours = "";
        if($hours > 0 ) {
            if($hours<10) {
                $hours = "0$hours";
            }
            $readableHours = $hours."h ";
        }
        if($minutes < 10 and $minutes > 0) {
            $minutes = "0$minutes";
        }
        if($minutes == 0) {
            $minutes = "00";
        }
        if($leftSeconds < 10 and $leftSeconds > 0) {
            $leftSeconds = "0$leftSeconds";
        }
        if($leftSeconds == 0) {
            $leftSeconds = "00";
        }
        if(!$showSeconds) {
            return $readableHours.$minutes;
        }
        return $readableHours.$minutes."m ".$leftSeconds."s";
    }


    //TIME
    public function humanTimeDiff( \DateTime $from, \DateTime $to) {
        $to = $to->getTimestamp();
        $from = $from->getTimestamp();
        $diff = (int) abs( $to - $from );
        $since = "";
        if ( $diff < 60 ) {
            $secs = $diff;
            if ( $secs <= 1 ) {
                $secs = 1;
            }
            $since = $secs. 'sec';
        } elseif ( $diff < 3600 && $diff >= 60 ) {
            $mins = round( $diff / 60 );
            if ( $mins <= 1 ) {
                $mins = 1;
            }
            $since = $mins .'min';
        } elseif ( $diff < 86400 && $diff >= 3600 ) {
            $hours = round( $diff / 3600 );
            if ( $hours <= 1 ) {
                $hours = 1;
            }
            $since = $hours .'h';
        } elseif ( $diff < 604800 && $diff >= 86400 ) {
            $days = round( $diff / 86400 );
            if ( $days <= 1 ) {
                $days = 1;
            }
            $since = $days.'j';
        } elseif ( $diff < 18144000 && $diff >= 604800 ) {
            $weeks = round( $diff / 604800 );
            if ( $weeks <= 1 ) {
                $weeks = 1;
            }
            $since = $weeks.'sem'; ;
        } elseif ( $diff < self::YEAR_IN_SECONDS && $diff >= self::MONTH_IN_SECONDS ) {
            $months = round( $diff / self::MONTH_IN_SECONDS );
            if ( $months <= 1 ) {
                $months = 1;
            }
            $since = $months.'mois';
        } elseif ( $diff >= self::YEAR_IN_SECONDS ) {
            $years = round( $diff / self::YEAR_IN_SECONDS );
            if ( $years <= 1 ) {
                $years = 1;
            }
            $since = $years.'ans';
        }
        return $since;
    }
}