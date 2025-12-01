<?php
namespace App\Services;

use App\Entity\Location;
use App\Entity\PrayNotification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PrayTimesService
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {
        $this->PrayTime(6);
    }

    private ?\DateTimeZone $currentTimezone = null;

    private array $offsetMinutes = [
        'fajr' => 0,
        'chorouq' => 0,
        'dohr' => 0,
        'asr' => 0,
        'maghrib' => 0,
        'icha' => 0,
    ];

    private const CALC_METHOD_IDS = [
        'JAFARI' => 0,
        'KARACHI' => 1,
        'ISNA' => 2,
        'MWL' => 3,
        'MAKKAH' => 4,
        'EGYPT' => 5,
        'CUSTOM' => 6,
        'TEHRAN' => 7,
    ];

    private const DEFAULT_METHOD_KEY = 'MWL';

    private const COUNTRY_METHODS = [
        'france' => 'CUSTOM',
        'tunisia' => 'MWL',
        'morocco' => 'MWL',
        'algeria' => 'MWL',
        'saudi arabia' => 'MAKKAH',
        'arabie saoudite' => 'MAKKAH',
    ];

    private const COUNTRY_OFFSETS = [
        'france' => [
            'fajr' => -38,
            'chorouq' => 1,
            'dohr' => 1,
            'asr' => 0,
            'maghrib' => 3,
            'icha' => 25,
        ],
    ];

    /**
     * City specific offsets in minutes, overriding country defaults when present.
     */
    private const CITY_OFFSETS = [
        'france' => [
            'angers' => [
                'fajr' => -5,
                'dohr' => 5,
                'asr' => -2,
                'maghrib' => 1,
                'icha' => 25,
            ],
            'bordeaux' => [
                'fajr' => -5,
                'dohr' => 5,
                'asr' => -1,
                'maghrib' => 0,
                'icha' => 4,
            ],
        ],
    ];


    const prays = [
        ['key' => 'fajr', 'label' => 'Al Fajr'],['key' => 'chorouq', 'label' => 'Chorouq'],['key' => 'dohr', 'label' => 'Duhur'],
        ['key' => 'asr', 'label' => 'Al Asr'],['key' => 'maghreb', 'label' => 'Maghrib'],['key' => 'icha', 'label' => 'Al Isha']
    ];




    public function getPrayTimesOfDay(User $currentUser, $day = null)
    {
        $userLocation = $currentUser->getLocation();
        if(is_null($userLocation)) {
            return [];
        }
        if(!is_null($day)) {
            $today =  $day;
        } else {
            $today =  new \DateTime('now');
        }
        $timestampday = $today->getTimestamp();
        $praytimes = $this->getUserPrayTimes($userLocation, $timestampday);
        return $this->getPrayTimesUI($currentUser, $praytimes, $timestampday, $userLocation);
    }

    public function getPrayTimesOfDayForLocation(Location $location, $day = null): array
    {
        if(!is_null($day)) {
            $today =  $day;
        } else {
            $today =  new \DateTime('now');
        }
        $timestampday = $today->getTimestamp();
        $praytimes = $this->getUserPrayTimes($location, $timestampday);

        return $this->getPrayTimesUI(null, $praytimes, $timestampday, $location);
    }

   public function getPrayTimesUI(?User $currentUser, $praytimes, $timestampday, Location $location)
{
   
    $praysN = [];
    if ($currentUser instanceof User) {
        $pn = $this->entityManager
            ->getRepository(PrayNotification::class)
            ->findOneBy(['user' => $currentUser]);
        if ($pn) {
            $praysN = $pn->getPrays() ?? [];
        }
    }

   
    $tz = $this->currentTimezone ?? $this->resolveTimezone($location);
    $date = (new \DateTimeImmutable('@' . $timestampday))->setTimezone($tz);

   
    $keep = [0,  2, 3, 5, 6];

   
    $map = [
        0 => 0,

        2 => 2,
        3 => 3,
        5 => 4,
        6 => 5,
    ];

    $praytimesUI = [];
    foreach ($praytimes as $index => $timeStr) {
        if (!in_array($index, $keep, true)) {
            continue;
        }

      
        $datestring = $date->format('Ymd') . ' ' . $timeStr;
        $prayDate = \DateTimeImmutable::createFromFormat('Ymd H:i', $datestring, $tz);
        if (!$prayDate) {
            continue;
        }

        $target = $map[$index];

        $praytimesUI[] = [
            'time'       => $timeStr,
            'timestamp'  => $prayDate->getTimestamp(),
            'label'      => self::prays[$target]['label'],
            'key'        => self::prays[$target]['key'],
            'isNotified' => in_array(self::prays[$target]['key'], $praysN, true),
        ];
    }

    return $praytimesUI;
}


public function getUserPrayTimes($userLocation, $timestampday) {
    $this->configureCalculationForLocation($userLocation, $timestampday);
    $lat = $userLocation->getLat();
    $lng = $userLocation->getLng();

    [$timezone, $tz] = $this->resolveTimezoneData($userLocation, $timestampday);
    $this->currentTimezone = $tz;

    return $this->getPrayerTimes($timestampday, $lat, $lng, $timezone);
}

    private function configureCalculationForLocation(Location $location, int $timestamp): void
    {
        $this->offsetMinutes = $this->resolveOffsets($location);
        $methodId = $this->resolveMethodId($location);
        $this->setCalcMethod($methodId);
    }

    private function resolveOffsets(Location $location): array
    {
        $base = [
            'fajr' => 0,
            'chorouq' => 0,
            'dohr' => 0,
            'asr' => 0,
            'maghrib' => 0,
            'icha' => 0,
        ];
        $countryKey = $this->normalizeLocationKey($location->getCountry());
        if(!is_null($countryKey) && isset(self::COUNTRY_OFFSETS[$countryKey])) {
            $base = array_merge($base, self::COUNTRY_OFFSETS[$countryKey]);
            $cityKey = $this->normalizeLocationKey($location->getCity());
            if(!is_null($cityKey) && isset(self::CITY_OFFSETS[$countryKey][$cityKey])) {
                $base = array_merge($base, self::CITY_OFFSETS[$countryKey][$cityKey]);
            }
        }
        return $base;
    }

    private function resolveMethodId(Location $location): int
    {
        $methodKey = self::DEFAULT_METHOD_KEY;
        $countryKey = $this->normalizeLocationKey($location->getCountry());
        if(!is_null($countryKey) && isset(self::COUNTRY_METHODS[$countryKey])) {
            $methodKey = self::COUNTRY_METHODS[$countryKey];
        }
        return self::CALC_METHOD_IDS[$methodKey] ?? self::CALC_METHOD_IDS[self::DEFAULT_METHOD_KEY];
    }

    private function resolveTimezoneData(Location $location, int $timestampday): array
    {
        $tz = $this->resolveTimezone($location);
        $day = (new \DateTimeImmutable('@' . $timestampday))->setTimezone($tz);
        $offsetSeconds = $tz->getOffset($day);
        $timezone = $offsetSeconds / 3600.0;

        return [$timezone, $tz];
    }

    private function resolveTimezone(Location $location): \DateTimeZone
    {
        $timezone = $location->getTimezone();
        if(is_string($timezone) && $timezone !== '') {
            try {
                return new \DateTimeZone($timezone);
            } catch (\Throwable $e) {
            }
        }
        return new \DateTimeZone('Etc/GMT-1');
    }


   
    var $Jafari     = 0;   
    var $Karachi    = 1;   
    var $ISNA       = 2;   
    var $MWL        = 3;   
    var $Makkah     = 4;   
    var $Egypt      = 5;   
    var $Custom     = 6;   
    var $Tehran     = 7;   

    var $Shafii     = 0;
    var $Hanafi     = 1;

    var $None       = 0;
    var $MidNight   = 1;
    var $OneSeventh = 2;    
    var $AngleBased = 3;   



    var $Time24     = 0;
    var $Time12     = 1;
    var $Time12NS   = 2;
    var $Float      = 3;

   
    var $timeNames = array(
        'Fajr',
        'Chorouq',
        'Dohr',
        'Asr',
        'Sunset',
        'Maghrib',
        'Isha'
    );

    var $InvalidTime = '-----';    


  


    var $calcMethod   = 0;      
    var $asrJuristic  = 0;      
    var $dhuhrMinutes = 0;      
    var $adjustHighLats = 1;  

    var $timeFormat   = 0;      

    var $lat;       
    var $lng;       
    var $timeZone;  
    var $JDate;    


 


    var $numIterations = 1;     


 


    var $methodParams = array();

  


    function PrayTime($methodID = 0)
    {

        $this->methodParams[$this->Jafari]    = array(16, 0, 4, 0, 14);
        $this->methodParams[$this->Karachi]   = array(18, 1, 0, 0, 18);
        $this->methodParams[$this->ISNA]      = array(15, 1, 0, 0, 15);
        $this->methodParams[$this->MWL]       = array(18, 1, 0, 0, 17);
        $this->methodParams[$this->Makkah]    = array(18.5, 1, 0, 1, 90);
        $this->methodParams[$this->Egypt]     = array(19.5, 1, 0, 0, 17.5);
        $this->methodParams[$this->Tehran]    = array(17.7, 0, 4.5, 0, 14);

        $this->methodParams[$this->Custom]    = array(12, 1, 0, 0, 12); //UOFI => 12°

        
        $this->setCalcMethod($methodID);
    }



  


  
    function getDatePrayerTimes($year, $month, $day, $latitude, $longitude, $timeZone)
    {
        $this->lat = $latitude;
        $this->lng = $longitude;
        $this->timeZone = $timeZone;
        $this->JDate = $this->julianDate($year, $month, $day)- $longitude/ (15* 24);
        return $this->computeDayTimes();
    }

   
    function getPrayerTimes($timestamp, $latitude, $longitude, $timeZone)
    {
        $date = @getdate($timestamp);
        return $this->getDatePrayerTimes($date['year'], $date['mon'], $date['mday'],
            $latitude, $longitude, $timeZone);
    }

  
    function setCalcMethod($methodID)
    {
        $this->calcMethod = $methodID;
    }

 
    function setAsrMethod($methodID)
    {
        if ($methodID < 0 || $methodID > 1)
            return;
        $this->asrJuristic = $methodID;
    }

 
    function setFajrAngle($angle)
    {
        $this->setCustomParams(array($angle, null, null, null, null));
    }

  
    function setMaghribAngle($angle)
    {
        $this->setCustomParams(array(null, 0, $angle, null, null));
    }

  
    function setIshaAngle($angle)
    {
        $this->setCustomParams(array(null, null, null, 0, $angle));
    }

  
    function setDhuhrMinutes($minutes)
    {
        $this->dhuhrMinutes = $minutes;
    }

  
    function setMaghribMinutes($minutes)
    {
        $this->setCustomParams(array(null, 1, $minutes, null, null));
    }

   
    function setIshaMinutes($minutes)
    {
        $this->setCustomParams(array(null, null, null, 1, $minutes));
    }

  
    function setCustomParams($params)
    {
        for ($i=0; $i<5; $i++)
        {
            if ($params[$i] == null)
                $this->methodParams[$this->Custom][$i] = $this->methodParams[$this->calcMethod][$i];
            else
                $this->methodParams[$this->Custom][$i] = $params[$i];
        }
        $this->calcMethod = $this->Custom;
    }

 
    function setHighLatsMethod($methodID)
    {
        $this->adjustHighLats = $methodID;
    }

    // set the time format
    function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
    }

  
    function floatToTime24($time)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60); 
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        return $this->twoDigitsFormat($hours). ':'. $this->twoDigitsFormat($minutes);
    }

    // convert float hours to 12h format
    function floatToTime12($time, $noSuffix = false)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        $suffix = $hours >= 12 ? ' pm' : ' am';
        $hours = ($hours+ 12- 1)% 12+ 1;
        return $hours. ':'. $this->twoDigitsFormat($minutes). ($noSuffix ? '' : $suffix);
    }

    // convert float hours to 12h format with no suffix
    function floatToTime12NS($time)
    {
        return $this->floatToTime12($time, true);
    }



  
    function sunPosition($jd)
    {
        $D = $jd - 2451545.0;
        $g = $this->fixangle(357.529 + 0.98560028* $D);
        $q = $this->fixangle(280.459 + 0.98564736* $D);
        $L = $this->fixangle($q + 1.915* $this->dsin($g) + 0.020* $this->dsin(2*$g));

        $R = 1.00014 - 0.01671* $this->dcos($g) - 0.00014* $this->dcos(2*$g);
        $e = 23.439 - 0.00000036* $D;

        $d = $this->darcsin($this->dsin($e)* $this->dsin($L));
        $RA = $this->darctan2($this->dcos($e)* $this->dsin($L), $this->dcos($L))/ 15;
        $RA = $this->fixhour($RA);
        $EqT = $q/15 - $RA;

        return array($d, $EqT);
    }

    // compute equation of time
    function equationOfTime($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[1];
    }

    // compute declination angle of sun
    function sunDeclination($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[0];
    }

    // compute mid-day (Dhuhr, Zawal) time
    function computeMidDay($t)
    {
        $T = $this->equationOfTime($this->JDate+ $t);
        $Z = $this->fixhour(12- $T);
        return $Z;
    }

    // compute time for a given angle G
    function computeTime($G, $t)
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $Z = $this->computeMidDay($t);
        $V = 1/15* $this->darccos((-$this->dsin($G)- $this->dsin($D)* $this->dsin($this->lat))/
                ($this->dcos($D)* $this->dcos($this->lat)));
        return $Z+ ($G>90 ? -$V : $V);
    }

    // compute the time of Asr
    function computeAsr($step, $t)  // Shafii: step=1, Hanafi: step=2
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $G = -$this->darccot($step+ $this->dtan(abs($this->lat- $D)));
        return $this->computeTime($G, $t);
    }


    //---------------------- Compute Prayer Times -----------------------


    // compute prayer times at given julian date
    function computeTimes($times)
    {
        $t = $this->dayPortion($times);

        $Fajr    = $this->computeTime(180- $this->methodParams[$this->calcMethod][0], $t[0]);
        $Sunrise = $this->computeTime(180- 0.833, $t[1]);
        $Dhuhr   = $this->computeMidDay($t[2]);
        $Asr     = $this->computeAsr(1+ $this->asrJuristic, $t[3]);
        $Sunset  = $this->computeTime(0.833, $t[4]);;
        $Maghrib = $this->computeTime($this->methodParams[$this->calcMethod][2], $t[5]);
        $Isha    = $this->computeTime($this->methodParams[$this->calcMethod][4], $t[6]);

        return array($Fajr, $Sunrise, $Dhuhr, $Asr, $Sunset, $Maghrib, $Isha);
    }


    // compute prayer times at given julian date
    function computeDayTimes()
    {
        $times = array(5, 6, 12, 13, 18, 18, 18); //default times

        for ($i=1; $i<=$this->numIterations; $i++)
            $times = $this->computeTimes($times);

        $times = $this->adjustTimes($times);
        return $this->adjustTimesFormat($times);
    }


    // adjust times in a prayer time array
    function adjustTimes($times)
    {
        for ($i=0; $i<7; $i++) {
            $times[$i] += $this->timeZone- $this->lng/ 15;
        }
        $times[2] += $this->dhuhrMinutes/ 60; //Dhuhr
        if ($this->methodParams[$this->calcMethod][1] == 1) // Maghrib
            $times[5] = $times[4]+ $this->methodParams[$this->calcMethod][2]/ 60;
        if ($this->methodParams[$this->calcMethod][3] == 1) // Isha
            $times[6] = $times[5]+ $this->methodParams[$this->calcMethod][4]/ 60;

        if ($this->adjustHighLats != $this->None) {
            $times = $this->adjustHighLatTimes($times);
        }
        $times[0] += $this->offsetMinutes['fajr']/60;
        $times[1] += $this->offsetMinutes['chorouq']/60;
        $times[2] += $this->offsetMinutes['dohr']/60;
        $times[3] += $this->offsetMinutes['asr']/60;
        $times[5] += $this->offsetMinutes['maghrib']/60;
        $times[6] += $this->offsetMinutes['icha']/60;
        return $times;
    }

    private function normalizeLocationKey(?string $value): ?string
    {
        if(!is_string($value)) {
            return null;
        }
        $trimmed = trim($value);
        if($trimmed === '') {
            return null;
        }
        return mb_strtolower($trimmed);
    }


    // convert times array to given time format
    function adjustTimesFormat($times)
    {
        if ($this->timeFormat == $this->Float)
            return $times;
        for ($i=0; $i<7; $i++)
            if ($this->timeFormat == $this->Time12)
                $times[$i] = $this->floatToTime12($times[$i]);
            else if ($this->timeFormat == $this->Time12NS)
                $times[$i] = $this->floatToTime12($times[$i], true);
            else
                $times[$i] = $this->floatToTime24($times[$i]);
        return $times;
    }


    // adjust Fajr, Isha and Maghrib for locations in higher latitudes
    function adjustHighLatTimes($times)
    {
        $nightTime = $this->timeDiff($times[4], $times[1]); // sunset to sunrise

        // Adjust Fajr
        $FajrDiff = $this->nightPortion($this->methodParams[$this->calcMethod][0])* $nightTime;
        if (is_nan($times[0]) || $this->timeDiff($times[0], $times[1]) > $FajrDiff)
            $times[0] = $times[1]- $FajrDiff;

        // Adjust Isha
        $IshaAngle = ($this->methodParams[$this->calcMethod][3] == 0) ? $this->methodParams[$this->calcMethod][4] : 18;
        $IshaDiff = $this->nightPortion($IshaAngle)* $nightTime;
        if (is_nan($times[6]) || $this->timeDiff($times[4], $times[6]) > $IshaDiff)
            $times[6] = $times[4]+ $IshaDiff;

        // Adjust Maghrib
        $MaghribAngle = ($this->methodParams[$this->calcMethod][1] == 0) ? $this->methodParams[$this->calcMethod][2] : 4;
        $MaghribDiff = $this->nightPortion($MaghribAngle)* $nightTime;
        if (is_nan($times[5]) || $this->timeDiff($times[4], $times[5]) > $MaghribDiff)
            $times[5] = $times[4]+ $MaghribDiff;

        return $times;
    }


    // the night portion used for adjusting times in higher latitudes
    function nightPortion($angle)
    {
        if ($this->adjustHighLats == $this->AngleBased)
            return 1/60* $angle;
        if ($this->adjustHighLats == $this->MidNight)
            return 1/2;
        if ($this->adjustHighLats == $this->OneSeventh)
            return 1/7;
    }


    // convert hours to day portions
    function dayPortion($times)
    {
        for ($i=0; $i<7; $i++)
            $times[$i] /= 24;
        return $times;
    }



  


    // compute the difference between two times
    function timeDiff($time1, $time2)
    {
        return $this->fixhour($time2- $time1);
    }


    // add a leading 0 if necessary
    function twoDigitsFormat($num)
    {
        return ($num <10) ? '0'. $num : $num;
    }



  


    // calculate julian date from a calendar date
    function julianDate($year, $month, $day)
    {
        if ($month <= 2)
        {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year/ 100);
        $B = 2- $A+ floor($A/ 4);

        $JD = floor(365.25* ($year+ 4716))+ floor(30.6001* ($month+ 1))+ $day+ $B- 1524.5;
        return $JD;
    }


    // convert a calendar date to julian date (second method)
    function calcJD($year, $month, $day)
    {
        $J1970 = 2440588.0;
        $date = $year. '-'. $month. '-'. $day;
        $ms = strtotime($date);   // # of milliseconds since midnight Jan 1, 1970
        $days = floor($ms/ (1000 * 60 * 60* 24));
        return $J1970+ $days- 0.5;
    }


    //---------------------- Trigonometric Functions -----------------------

    // degree sin
    function dsin($d)
    {
        return sin($this->dtr($d));
    }

    // degree cos
    function dcos($d)
    {
        return cos($this->dtr($d));
    }

    // degree tan
    function dtan($d)
    {
        return tan($this->dtr($d));
    }

    // degree arcsin
    function darcsin($x)
    {
        return $this->rtd(asin($x));
    }

    // degree arccos
    function darccos($x)
    {
        return $this->rtd(acos($x));
    }

    // degree arctan
    function darctan($x)
    {
        return $this->rtd(atan($x));
    }

    // degree arctan2
    function darctan2($y, $x)
    {
        return $this->rtd(atan2($y, $x));
    }

    // degree arccot
    function darccot($x)
    {
        return $this->rtd(atan(1/$x));
    }

    // degree to radian
    function dtr($d)
    {
        return ($d * M_PI) / 180.0;
    }

    // radian to degree
    function rtd($r)
    {
        return ($r * 180.0) / M_PI;
    }

    // range reduce angle in degrees.
    function fixangle($a)
    {
        $a = $a - 360.0 * floor($a / 360.0);
        $a = $a < 0 ? $a + 360.0 : $a;
        return $a;
    }

    // range reduce hours to 0..23
    function fixhour($a)
    {
        $a = $a - 24.0 * floor($a / 24.0);
        $a = $a < 0 ? $a + 24.0 : $a;
        return $a;
    }
}
