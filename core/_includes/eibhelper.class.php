<?php
function DptSelectEncode($dpt, $value, $inverse) {
    $All_DPT = All_DPT();
    $type    = substr($dpt, 0, strpos($dpt, '.'));
    switch ($type) {
        case "1":
            if ($value != 0 && $value != 1) {
                $ValeurDpt = $All_DPT["Boolean"][$dpt][Valeurs];
                $value     = array_search($value, $ValeurDpt);
            }
            if ($inverse) {
                if ($value == 0)
                    $value = 1;
                else
                    $value = 0;
            }
            $data = $value;
            break;
        case "2":
            $data = $value;
            break;
        case "3":
            $ctrl = 1;
            if ($value > 0)
                $stepCode = abs($value) & 0x07;
            $data = $ctrl << 3 | $stepCode;
            break;
        case "5":
            switch ($dpt) {
                case "5.001":
                    $value = round(intval($value) * 255 / 100);
                    break;
                case "5.003":
                    $value = round(intval($value) * 255 / 360);
                    break;
                case "5.004":
                    $value = round(intval($value) * 255);
                    break;
            }
            $data = array(
                $value
            );
            break;
        case "6":
            if ($value < 0)
                $value = (abs($value) ^ 0xff) + 1; # twos complement
            $data = array(
                $value
            );
            break;
        case "7":
            $data = array(
                $value
            );
            break;
        case "8":
            /*      if data >= 0x8000:
            data = -((data - 1) ^ 0xffff)  # invert twos complement
            else:
            data = data
            if self._dpt is self.DPT_DeltaTime10Msec:
            value = data * 10.
            elif self._dpt is self.DPT_DeltaTime100Msec:
            value =data * 100.
            elif self._dpt is self.DPT_Percent_V16:
            value = data / 100.
            else:
            value = data*/
            $data = array(
                $value
            );
            break;
        case "9":
            if ($value < 0) {
                $sign  = 1;
                $value = -$value;
            } else
                $sign = 0;
            $value = $value * 100.0;
            $exp   = 0;
            while ($value > 2047) {
                $exp++;
                $value = $value / 2;
            }
            if ($sign)
                $value = -$value;
            $value = $value & 0x7ff;
            $data  = array(
                ($sign << 7) | (($exp & 0x0f) << 3) | (($value >> 8) & 0x07),
                ($value & 0xff)
            );
            break;
        case "10":
            $value = new DateTime($value);
            $wDay  = $value->format('N');
            $hour  = $value->format('H');
            $min   = $value->format('i');
            $sec   = $value->format('s');
            $data  = array(
                ($wDay << 5) | $hour,
                $min,
                $sec
            );
            break;
        case "11":
            $value = new DateTime($value);
            $day   = $value->format('d');
            $month = $value->format('m');
            $year  = $value->format('y');
            $data  = array(
                $day,
                $month,
                $year
            );
            break;
        case "12":
            $data = array(
                $value
            );
            break;
        case "13":
            if ($value < 0)
                $value = (abs($value) ^ 0xffffffff) + 1; # twos complement
            /* if self._dpt is self.DPT_Value_FlowRate_m3h:
            $data = int(round($value * 10000.))
            else*/
            $data = array(
                ($value >> 24) & 0xFF,
                ($value >> 16) & 0xFF,
                ($value >> 8) & 0xFF,
                $value & 0xFF
            );
            break;
        case "14":
            $value = unpack("L", pack("f", $value));
            $data  = array(
                ($value[1] >> 24) & 0xff,
                ($value[1] >> 16) & 0xff,
                ($value[1] >> 8) & 0xff,
                $value[1] & 0xff
            );
            break;
        case "16":
            /*data = 0x00
            for shift in range(104, -1, -8):
            data |= value[13 - shift / 8] << shift*/
            $data = array(
                $value
            );
            break;
        case "17":
            /*ctrl = value[0]
            scene = value[1]
            data = ctrl << 7 | scene*/
            $data = array(
                $value
            );
            break;
        case "19":
            $value = new DateTime($value);
            $wDay  = $value->format('N');
            $hour  = $value->format('H');
            $min   = $value->format('i');
            $sec   = $value->format('s');
            $day   = $value->format('d');
            $month = $value->format('m');
            $year  = $value->format('Y') - 1900;
            $data  = array(
                $year,
                $month,
                $day,
                ($wDay << 5) | $hour,
                $min,
                $sec,
                0,
                0
            );
            break;
        case "20":
            if ($dpt != "20.xxx") {
                $ValeurDpt = $All_DPT["8BitEncAbsValue"][$dpt]["Valeurs"];
                $value     = array_search($value, $ValeurDpt);
            }
            $data = array(
                $value
            );
            break;
    }
    ;
    return $data;
}
function DptSelectDecode($dpt, $data, $inverse) {
    $All_DPT = All_DPT();
    $type    = substr($dpt, 0, strpos($dpt, '.'));
    switch ($type) {
        case "1":
            $value = $data;
            if ($inverse) {
                if ($value == 0)
                    $value = 1;
                else
                    $value = 0;
            }
            break;
        case "2":
            $value = $data;
            break;
        case "3":
            $ctrl     = ($data & 0x08) >> 3;
            $stepCode = $data & 0x07;
            if ($ctrl)
                $value = $stepCode;
            else
                $value = -$stepCode;
            break;
        case "5":
            switch ($dpt) {
                case "5.001":
                    $value = round((intval($data[0]) * 100) / 255);
                    break;
                case "5.003":
                    $value = round((intval($data[0]) * 360) / 255);
                    break;
                case "5.004":
                    $value = round(intval($data[0]) / 255);
                    break;
                default:
                    $value = intval($data[0]);
                    break;
            }
            break;
        case "6":
            if ($data[0] >= 0x80)
                $value = -(($data[0] - 1) ^ 0xff); # invert twos complement
            else
                $value = $data[0];
            break;
        case "7":
            /* if self._dpt is self.DPT_TimePeriod10Msec:
            $value = data * 10.
            elif self._dpt is self.DPT_TimePeriod100Msec:
            $value = data * 100.
            else:*/
            $value = $data;
            break;
        case "8":
            if ($data[0] >= 0x8000)
                $data[0] = -(($data - 1) ^ 0xffff); # invert twos complement
            /*if self._dpt is self.DPT_DeltaTime10Msec:
            $value = data * 10.
            elif self._dpt is self.DPT_DeltaTime100Msec:
            $value =data * 100.
            elif self._dpt is self.DPT_Percent_V16:
            $value = data / 100.
            else:*/
            $value = $data[0];
            break;
        case "9":
            $exp  = ($data[0] & 0x78) >> 3;
            $sign = ($data[0] & 0x80) >> 7;
            $mant = ($data[0] & 0x07) << 8 | $data[1];
            if ($sign)
                $sign = -1 << 11;
            else
                $sign = 0;
            $value = ($mant | $sign) * pow(2, $exp) * 0.01;
            break;
        case "10":
            $wDay  = ($data[0] >> 5) & 0x07;
            $hour  = $data[0] & 0x1f;
            $min   = $data[1] & 0x3f;
            $sec   = $data[2] & 0x3f;
            $value = /*new DateTime(*/ $hour . ':' . $min . ':' . $sec; //);
            break;
        case "11":
            $day   = $data[0] & 0x1f;
            $month = $data[1] & 0x0f;
            $year  = $data[2] & 0x7f;
            if ($year < 90)
                $year += 2000;
            else
                $year += 1900;
            $value = /* new DateTime(*/ $day . '/' . $month . '/' . $year; //);
            break;
        case "12":
            $value = $data[0];
            break;
        case "13":
            $value = $data[0] << 24 | $data[1] << 16 | $data[2] << 8 | $data[3];
            if ($value >= 0x80000000)
                $value = -(($value - 1) ^ 0xffffffff); # invert twos complement           
            break;
        case "14":
            $value = $data[0] << 24 | $data[1] << 16 | $data[2] << 8 | $data[3];
            $value = unpack("f", pack("L", $value))[1];
            break;
        case "16":
            // $value = tuple([int((data >> shift) & 0xff) for shift in range(104, -1, -8)])
            break;
        case "17":
            $ctrl  = ($data[0] >> 7) & 0x01;
            $scene = $data[0] & 0x3f;
            //  $value = ($ctrl, $scene);
            break;
        case "19":
            $year           = $data[0] + 1900;
            $month          = $data[1];
            $day            = $data[2];
            $wDay           = ($data[3] >> 5) & 0x07;
            $hour           = $data[3] & 0x1f;
            $min            = $data[4] & 0x3f;
            $sec            = $data[5] & 0x3f;
            $Fault          = ($data[6] >> 7) & 0x01;
            $WorkingDay     = ($data[6] >> 6) & 0x01;
            $noWorkingDay   = ($data[6] >> 5) & 0x01;
            $noYear         = ($data[6] >> 4) & 0x01;
            $noDate         = ($data[6] >> 3) & 0x01;
            $noDayOfWeek    = ($data[6] >> 2) & 0x01;
            $NoTime         = ($data[6] >> 1) & 0x01;
            $SummerTime     = $data[6] & 0x01;
            $QualityOfClock = ($data[7] >> 7) & 0x01;
            $value          = new DateTime();
            $value->setDate($year, $month, $day);
            $value->setTime($hour, $min, $sec);
            break;
        case "20":
            $value = $data[0];
            if ($dpt != "20.xxx") {
                if ($dpt == "20.102_2") {
                    if (dechex($value) > 0x80)
                        $value = dechex($value) - 0x80;
                    if (dechex($value) > 0x20)
                        $value = dechex($value) - 0x20;
                    switch ($value) {
                        case "1":
                            $value = "Comfort";
                            break;
                        case "2":
                            $value = "Standby";
                            break;
                        case "4":
                            $value = "Night";
                            break;
                        case "8":
                            $value = "Frost";
                            break;
                    }
                } else
                    $value = $All_DPT["8BitEncAbsValue"][$dpt]["Valeurs"][$data[0]];
            }
            break;
    }
    ;
    return $value;
}
function OtherValue($dpt, $oldValue) {
    $All_DPT = All_DPT();
    $type    = substr($dpt, 0, strpos($dpt, '.'));
    switch ($type) {
        case "1":
            if ($oldValue == 1)
                $value = 0;
            else
                $value = 1;
            break;
        case "2":
            break;
        case "3":
            break;
        case "4":
            break;
        case "5":
            break;
        case "6":
            break;
        case "7":
            break;
        case "8":
            break;
        case "9":
            $value = $oldValue + 1;
            break;
        case "10":
            $value = new DateTime();
            break;
        case "11":
            $value = new DateTime();
            break;
        case "12":
            break;
        case "13":
            break;
        case "14":
            break;
        case "15":
            break;
        case "16":
            break;
        case "17":
            break;
        case "18":
            break;
        case "19":
            break;
        case "20":
            $value = $oldValue + 1;
            break;
    }
    ;
    return $value;
}
function EibdRead($con, $addr) {
    $addr = gaddrparse($addr);
    
    if ($con->EIBOpenT_Group($addr, 0) == -1)
        throw new Exception(__('Erreur de connexion au Bus KNX', __FILE__));
    $data = chr(0) . chr(0);
    $len  = $con->EIBSendAPDU($data);
    if ($len == -1)
        throw new Exception(__('Impossible de lire la valeur', __FILE__));
    while (1) {
        $data = new EIBBuffer();
        $src  = new EIBAddr();
        $len  = $con->EIBGetAPDU_Src($data, $src);
        if ($len == -1)
            throw new Exception(__('Impossible de lire la valeur', __FILE__));
        if ($len < 2)
            throw new Exception(__('Paquet Invalide', __FILE__));
        $buf = unpack("C*", $data->buffer);
        if ($buf[1] & 0x3 || ($buf[2] & 0xC0) == 0xC0) {
            throw new Exception(__("Error: Unknown APDU: " . $buf[1] . "X" . $buf[2], __FILE__));
        } else if (($buf[2] & 0xC0) == 0x40) {
            if ($len == 2) {
                $return = $buf[2] & 0x3F;
            } else {
                $return = array_slice($buf, 2);
            }
            break;
        }
        
    }
    $con->EIBReset();
    return $return; // return the EIB status, 0=Off, 1=On, xx-dimmer value
}
function parseread($len, $buf) {
    $buf = unpack("C*", $buf->buffer);
    if ($buf[1] & 0x3 || ($buf[2] & 0xC0) == 0xC0)
        log::add('eibd', 'error', "Error: Unknown APDU: " . $buf[1] . "X" . $buf[2]);
    else if (($buf[2] & 0xC0) == 0x00) {
        if ($len == 2)
            return array(
                "Read",
                $buf[2] & 0x3F
            );
        else
            return array(
                "Read",
                array_slice($buf, 2)
            );
    } else if (($buf[2] & 0xC0) == 0x40) {
        if ($len == 2)
            return array(
                "Response",
                $buf[2] & 0x3F
            );
        else
            return array(
                "Response",
                array_slice($buf, 2)
            );
    } else if (($buf[2] & 0xC0) == 0x80) {
        if ($len == 2)
            return array(
                "Write",
                $buf[2] & 0x3F
            );
        else
            return array(
                "Write",
                array_slice($buf, 2)
            );
    }
}
function gaddrparse($addr) {
    $addr = split("/", $addr);
    if (count($addr) >= 3)
        $r = (($addr[0] & 0x1f) << 11) | (($addr[1] & 0x7) << 8) | (($addr[2] & 0xff));
    if (count($addr) == 2)
        $r = (($addr[0] & 0x1f) << 11) | (($addr[1] & 0x7ff));
    if (count($addr) == 1)
        $r = (($addr[1] & 0xffff));
    return $r;
}
function formatiaddr($addr) {
    return sprintf("%d.%d.%d", ($addr >> 12) & 0x0f, ($addr >> 8) & 0x0f, ($addr >> 0) & 0xff);
}
function formatgaddr($addr) {
    return sprintf("%d/%d/%d", ($addr >> 11) & 0x1f, ($addr >> 8) & 0x07, ($addr >> 0) & 0xff);
}
function EibdWrite($con, $addr, $val) {
    //if(count($val)==1 && $val[0]<0x3F)
    if (!is_array($val)) {
        log::add('eibd', 'debug', 'groupswrite');
        $val = ($val + 0) & 0x3f;
        $val |= 0x0080;
        $data = pack("n", $val);
    } else {
        $header = 0x0080;
        $data   = pack("n", $header);
        for ($i = 0; $i < count($val); $i++)
            $data .= pack("C", $val[$i]);
    }
    $addr = gaddrparse($addr);
    $r    = $con->EIBOpenT_Group($addr, 1);
    if ($r == -1)
        return -1;
    $r = $con->EIBSendAPDU($data);
    if ($r == -1)
        return -1;
    return $con->EIBReset();
}
function groupswrite($con, $addr, $val) {
    $addr = gaddrparse($addr);
    $val  = ($val[0] + 0) & 0x3f;
    $val |= 0x0080;
    $r = $con->EIBOpenT_Group($addr, 1);
    if ($r == -1)
        return -1;
    $r = $con->EIBSendAPDU(pack("n", $val));
    if ($r == -1)
        return -1;
    return $con->EIBReset();
}
function groupwrite($con, $addr, $val) {
    $addr   = gaddrparse($addr);
    $header = 0x0080;
    $r      = $con->EIBOpenT_Group($addr, 1);
    if ($r == -1)
        return -1;
    $data = pack("n", $header);
    for ($i = 0; $i < count($val); $i++)
        $data .= pack("C", $val[$i]);
    $r = $con->EIBSendAPDU($data);
    if ($r == -1)
        return -1;
    return $con->EIBReset();
}
function getDptUnite($dpt) {
    $All_DPT = All_DPT();
    while ($Type = current($All_DPT)) {
        while ($Dpt = current($Type)) {
            if ($dpt == key($Type))
                return $Dpt["Unite"];
            next($Type);
        }
        next($All_DPT);
    }
    return;
}
function All_DPT() {
    return array(
        "Boolean" => array(
            "1.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    1
                ),
                "Unite" => ""
            ),
            "1.001" => array(
                "Name" => "Switch",
                "Valeurs" => array(
                    "Off",
                    "On"
                ),
                "Unite" => ""
            ),
            "1.002" => array(
                "Name" => "Boolean",
                "Valeurs" => array(
                    "False",
                    "True"
                ),
                "Unite" => ""
            ),
            "1.003" => array(
                "Name" => "Enable",
                "Valeurs" => array(
                    "Disable",
                    "Enable"
                ),
                "Unite" => ""
            ),
            "1.004" => array(
                "Name" => "Ramp",
                "Valeurs" => array(
                    "No ramp",
                    "Ramp"
                ),
                "Unite" => ""
            ),
            "1.005" => array(
                "Name" => "Alarm",
                "Valeurs" => array(
                    "No alarm",
                    "Alarm"
                ),
                "Unite" => ""
            ),
            "1.006" => array(
                "Name" => "Binary value",
                "Valeurs" => array(
                    "Low",
                    "High"
                ),
                "Unite" => ""
            ),
            "1.007" => array(
                "Name" => "Step",
                "Valeurs" => array(
                    "Decrease",
                    "Increase"
                ),
                "Unite" => ""
            ),
            "1.008" => array(
                "Name" => "Up/Down",
                "Valeurs" => array(
                    "Up",
                    "Down"
                ),
                "Unite" => ""
            ),
            "1.009" => array(
                "Name" => "Open/Close",
                "Valeurs" => array(
                    "Open",
                    "Close"
                ),
                "Unite" => ""
            ),
            "1.010" => array(
                "Name" => "Start",
                "Valeurs" => array(
                    "Stop",
                    "Start"
                ),
                "Unite" => ""
            ),
            "1.011" => array(
                "Name" => "State",
                "Valeurs" => array(
                    "Inactive",
                    "Active"
                ),
                "Unite" => ""
            ),
            "1.012" => array(
                "Name" => "Invert",
                "Valeurs" => array(
                    "Not inverted",
                    "Inverted"
                ),
                "Unite" => ""
            ),
            "1.013" => array(
                "Name" => "Dimmer send-style",
                "Valeurs" => array(
                    "Start/stop",
                    "Cyclically"
                ),
                "Unite" => ""
            ),
            "1.014" => array(
                "Name" => "Input source",
                "Valeurs" => array(
                    "Fixed",
                    "Calculated"
                ),
                "Unite" => ""
            ),
            "1.015" => array(
                "Name" => "Reset",
                "Valeurs" => array(
                    "No action",
                    "Reset"
                ),
                "Unite" => ""
            ),
            "1.016" => array(
                "Name" => "Acknowledge",
                "Valeurs" => array(
                    "No action",
                    "Acknowledge"
                ),
                "Unite" => ""
            ),
            "1.017" => array(
                "Name" => "Trigger",
                "Valeurs" => array(
                    "Trigger",
                    "Trigger"
                ),
                "Unite" => ""
            ),
            "1.018" => array(
                "Name" => "Occupancy",
                "Valeurs" => array(
                    "Not occupied",
                    "Occupied"
                ),
                "Unite" => ""
            ),
            "1.019" => array(
                "Name" => "Window/Door",
                "Valeurs" => array(
                    "Closed",
                    "Open"
                ),
                "Unite" => ""
            ),
            "1.021" => array(
                "Name" => "Logical function",
                "Valeurs" => array(
                    "OR",
                    "AND"
                ),
                "Unite" => ""
            ),
            "1.022" => array(
                "Name" => "Scene A/B",
                "Valeurs" => array(
                    "Scene A",
                    "Scene B"
                ),
                "Unite" => ""
            ),
            "1.023" => array(
                "Name" => "Shutter/Blinds mode",
                "Valeurs" => array(
                    "Only move Up/Down",
                    "Move Up/Down + StepStop"
                ),
                "Unite" => ""
            )
        ),
        "1BitPriorityControl" => array(
            "2.001" => array(
                "Name" => "DPT_Switch_Control",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.002" => array(
                "Name" => "DPT_Bool_Control",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.003" => array(
                "Name" => "DPT_Enable_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.004" => array(
                "Name" => "DPT_Ramp_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.005" => array(
                "Name" => "DPT_Alarm_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.006" => array(
                "Name" => "DPT_BinaryValue_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.007" => array(
                "Name" => "DPT_Step_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.010" => array(
                "Name" => "DPT_Start_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.011" => array(
                "Name" => "DPT_State_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "2.012" => array(
                "Name" => "DPT_Invert_Controll",
                "Valeurs" => array(),
                "Unite" => ""
            )
        ),
        "3BitControl" => array(
            "3.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -7,
                    7
                ),
                "Unite" => ""
            ),
            "3.007" => array(
                "Name" => "Dimming",
                "Valeurs" => array(
                    -7,
                    7
                ),
                "Unite" => ""
            ),
            "3.008" => array(
                "Name" => "Blinds",
                "Valeurs" => array(
                    -7,
                    7
                ),
                "Unite" => ""
            )
        ),
        "8BitUnsigned" => array(
            "5.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    255
                ),
                "Unite" => ""
            ),
            "5.001" => array(
                "Name" => "Scaling",
                "Valeurs" => array(
                    0,
                    100
                ),
                "Unite" => "%"
            ),
            "5.003" => array(
                "Name" => "Angle",
                "Valeurs" => array(
                    0,
                    360
                ),
                "Unite" => "°"
            ),
            "5.004" => array(
                "Name" => "Percent (8 bit)",
                "Valeurs" => array(
                    0,
                    255
                ),
                "Unite" => "%"
            ),
            "5.005" => array(
                "Name" => "Decimal factor",
                "Valeurs" => array(
                    0,
                    1
                ),
                "Unite" => "ratio"
            ),
            // 	"5.006"=> array(
            //		"Name"=>"Tariff",
            //		"Valeurs"=>array(0, 254),
            //		"Unite"=>"ratio"),
            "5.010" => array(
                "Name" => "Unsigned count",
                "Valeurs" => array(
                    0,
                    255
                ),
                "Unite" => "pulses"
            )
        ),
        "8BitSigned" => array(
            "6.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -128,
                    127
                ),
                "Unite" => ""
            ),
            "6.001" => array(
                "Name" => "Percent (8 bit)",
                "Valeurs" => array(
                    -128,
                    127
                ),
                "Unite" => "%"
            ),
            "6.010" => array(
                "Name" => "Signed count",
                "Valeurs" => array(
                    -128,
                    127
                ),
                "Unite" => "pulses"
            )
        ),
        "2ByteUnsigned" => array(
            "7.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => ""
            ),
            "7.001" => array(
                "Name" => "Unsigned count",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "pulses"
            ),
            "7.002" => array(
                "Name" => "Time period (resol. 1ms)",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "ms"
            ),
            "7.003" => array(
                "Name" => "Time period (resol. 10ms)",
                "Valeurs" => array(
                    0,
                    655350
                ),
                "Unite" => "ms"
            ),
            "7.004" => array(
                "Name" => "Time period (resol. 100ms)",
                "Valeurs" => array(
                    0,
                    6553500
                ),
                "Unite" => "ms"
            ),
            "7.005" => array(
                "Name" => "Time period (resol. 1s)",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "s"
            ),
            "7.006" => array(
                "Name" => "Time period (resol. 1min)",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "min"
            ),
            "7.007" => array(
                "Name" => "Time period (resol. 1h)",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "h"
            ),
            "7.010" => array(
                "Name" => "Interface object property ID",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => ""
            ),
            "7.011" => array(
                "Name" => "Length",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "mm"
            ),
            //	"7.012"=> array(
            //		"Name"=>"Electrical current",
            //		"Valeurs"=>array(0, 65535),
            //		"Unite"=>"mA")  # Add special meaning for 0 (create Limit object),
            "7.013" => array(
                "Name" => "Brightness",
                "Valeurs" => array(
                    0,
                    65535
                ),
                "Unite" => "lx"
            )
        ),
        "2ByteSigned" => array(
            "8.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => ""
            ),
            "8.001" => array(
                "Name" => "Signed count",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "pulses"
            ),
            "8.002" => array(
                "Name" => "Delta time (ms)",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "ms"
            ),
            "8.003" => array(
                "Name" => "Delta time (10ms)",
                "Valeurs" => array(
                    -327680,
                    327670
                ),
                "Unite" => "ms"
            ),
            "8.004" => array(
                "Name" => "Delta time (100ms)",
                "Valeurs" => array(
                    -3276800,
                    3276700
                ),
                "Unite" => "ms"
            ),
            "8.005" => array(
                "Name" => "Delta time (s)",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "s"
            ),
            "8.006" => array(
                "Name" => "Delta time (min)",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "min"
            ),
            "8.007" => array(
                "Name" => "Delta time (h)",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "h"
            ),
            "8.010" => array(
                "Name" => "Percent (16 bit)",
                "Valeurs" => array(
                    -327.68,
                    327.67
                ),
                "Unite" => "%"
            ),
            "8.011" => array(
                "Name" => "Rotation angle",
                "Valeurs" => array(
                    -32768,
                    32767
                ),
                "Unite" => "°"
            )
        ),
        "2ByteFloat" => array(
            "9.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -671088.64,
                    +670760.96
                ),
                "Unite" => ""
            ),
            "9.001" => array(
                "Name" => "Temperature",
                "Valeurs" => array(
                    -273.,
                    +670760.
                ),
                "Unite" => "°C"
            ),
            "9.002" => array(
                "Name" => "Temperature difference",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "K"
            ),
            "9.003" => array(
                "Name" => "Temperature gradient",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "K/h"
            ),
            "9.004" => array(
                "Name" => "Luminous emittance",
                "Valeurs" => array(
                    0.,
                    +670760.
                ),
                "Unite" => "lx"
            ),
            "9.005" => array(
                "Name" => "Wind speed",
                "Valeurs" => array(
                    0.,
                    +670760.
                ),
                "Unite" => "m/s"
            ),
            "9.006" => array(
                "Name" => "Air pressure",
                "Valeurs" => array(
                    0.,
                    +670760.
                ),
                "Unite" => "Pa"
            ),
            "9.007" => array(
                "Name" => "Humidity",
                "Valeurs" => array(
                    0.,
                    +670760.
                ),
                "Unite" => "%"
            ),
            "9.008" => array(
                "Name" => "Air quality",
                "Valeurs" => array(
                    0.,
                    +670760.
                ),
                "Unite" => "ppm"
            ),
            "9.010" => array(
                "Name" => "Time difference 1",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "s"
            ),
            "9.011" => array(
                "Name" => "Time difference 2",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "ms"
            ),
            "9.020" => array(
                "Name" => "Electrical voltage",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "mV"
            ),
            "9.021" => array(
                "Name" => "Electric current",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "mA"
            ),
            "9.022" => array(
                "Name" => "Power density",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "W/m²"
            ),
            "9.023" => array(
                "Name" => "Kelvin/percent",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "K/%"
            ),
            "9.024" => array(
                "Name" => "Power",
                "Valeurs" => array(
                    -670760.,
                    +670760.
                ),
                "Unite" => "kW"
            ),
            "9.025" => array(
                "Name" => "Volume flow",
                "Valeurs" => array(
                    -670760.,
                    670760.
                ),
                "Unite" => "l/h"
            ),
            "9.026" => array(
                "Name" => "Rain amount",
                "Valeurs" => array(
                    -670760.,
                    670760.
                ),
                "Unite" => "l/m²"
            ),
            "9.027" => array(
                "Name" => "Temperature (°F)",
                "Valeurs" => array(
                    -459.6,
                    670760.
                ),
                "Unite" => "°F"
            ),
            "9.028" => array(
                "Name" => "Wind speed (km/h)",
                "Valeurs" => array(
                    0.,
                    670760.
                ),
                "Unite" => "km/h"
            )
        ),
        "Time" => array(
            "10.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    16777215
                ),
                "Unite" => ""
            ),
            "10.001" => array(
                "Name" => "Time of day",
                "Valeurs" => array(
                    array(
                        0,
                        0,
                        0,
                        0
                    ),
                    array(
                        7,
                        23,
                        59,
                        59
                    )
                ),
                "Unite" => ""
            )
        ),
        "Date" => array(
            "11.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    16777215
                ),
                "Unite" => ""
            ),
            "11.001" => array(
                "Name" => "Date",
                "Valeurs" => array(
                    array(
                        1,
                        1,
                        1969
                    ),
                    array(
                        31,
                        12,
                        2068
                    )
                ),
                "Unite" => ""
            )
        ),
        "4ByteUnsigned" => array(
            "12.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    4294967295
                ),
                "Unite" => ""
            ),
            "12.001" => array(
                "Name" => "Unsigned count",
                "Valeurs" => array(
                    0,
                    4294967295
                ),
                "Unite" => "pulses"
            )
        ),
        "4ByteSigned" => array(
            "13.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -2147483648,
                    2147483647
                ),
                "Unite" => ""
            ),
            "13.001" => array(
                "Name" => "Signed count",
                "Valeurs" => array(
                    -2147483648,
                    2147483647
                ),
                "Unite" => "pulses"
            ),
            "13.001" => array(
                "Name" => "Flow rate",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "m³/h"
            ),
            "13.010" => array(
                "Name" => "Active energy",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "W.h"
            ),
            "13.011" => array(
                "Name" => "Apparent energy",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "VA.h"
            ),
            "13.012" => array(
                "Name" => "Reactive energy",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "VAR.h"
            ),
            "13.013" => array(
                "Name" => "Active energy (kWh)",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "kW.h"
            ),
            "13.014" => array(
                "Name" => "Apparent energy (kVAh)",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "kVA.h"
            ),
            "13.015" => array(
                "Name" => "Reactive energy (kVARh)",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "kVAR.h"
            ),
            "13.100" => array(
                "Name" => "Long delta time",
                "Valeurs" => array(
                    -214748.3648,
                    214748.3647
                ),
                "Unite" => "s"
            )
        ),
        "4ByteFloat" => array(
            "14.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => ""
            ),
            //	"14.xxx"=> array(
            //		"Name"=>"Generic",
            //		"Valeurs"=>array(-340282346638528859811704183484516925440, 340282346638528859811704183484516925440)
            //		"Unite"=>""),
            "14.000" => array(
                "Name" => "Acceleration",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m/s²"
            ),
            "14.001" => array(
                "Name" => "Acceleration, angular",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "rad/s²"
            ),
            "14.002" => array(
                "Name" => "Activation energy",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J/mol"
            ),
            "14.003" => array(
                "Name" => "Activity (radioactive)",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "s⁻¹"
            ),
            "14.004" => array(
                "Name" => "Amount of substance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "mol"
            ),
            "14.005" => array(
                "Name" => "Amplitude",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => ""
            ),
            "14.006" => array(
                "Name" => "Angle, radiant",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "rad"
            ),
            "14.007" => array(
                "Name" => "Angle, degree",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "°"
            ),
            "14.008" => array(
                "Name" => "Angular momentum",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J.s"
            ),
            "14.009" => array(
                "Name" => "Angular velocity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "rad/s"
            ),
            "14.010" => array(
                "Name" => "Area",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m²"
            ),
            "14.011" => array(
                "Name" => "Capacitance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "F"
            ),
            "14.012" => array(
                "Name" => "Charge density (surface)",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C/m²"
            ),
            "14.013" => array(
                "Name" => "Charge density (volume)",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C/m³"
            ),
            "14.014" => array(
                "Name" => "Compressibility",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m²/N"
            ),
            "14.015" => array(
                "Name" => "Conductance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "S"
            ),
            "14.016" => array(
                "Name" => "Conductivity, electrical",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "S/m"
            ),
            "14.017" => array(
                "Name" => "Density",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "kg/m³"
            ),
            "14.018" => array(
                "Name" => "Electric charge",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C"
            ),
            "14.019" => array(
                "Name" => "Electric current",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A"
            ),
            "14.020" => array(
                "Name" => "Electric current density",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A/m²"
            ),
            "14.021" => array(
                "Name" => "Electric dipole moment",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Cm"
            ),
            "14.022" => array(
                "Name" => "Electric displacement",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C/m²"
            ),
            "14.023" => array(
                "Name" => "Electric field strength",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "V/m"
            ),
            "14.024" => array(
                "Name" => "Electric flux",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "c"
            ), # unit??? C
            "14.025" => array(
                "Name" => "Electric flux density",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C/m²"
            ),
            "14.026" => array(
                "Name" => "Electric polarization",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "C/m²"
            ),
            "14.027" => array(
                "Name" => "Electric potential",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "V"
            ),
            "14.028" => array(
                "Name" => "Electric potential difference",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "V"
            ),
            "14.029" => array(
                "Name" => "Electromagnetic moment",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A.m²"
            ),
            "14.030" => array(
                "Name" => "Electromotive force",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "V"
            ),
            "14.031" => array(
                "Name" => "Energy",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J"
            ),
            "14.032" => array(
                "Name" => "Force",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "N"
            ),
            "14.033" => array(
                "Name" => "Frequency",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Hz"
            ),
            "14.034" => array(
                "Name" => "Frequency, angular (pulsatance)",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "rad/s"
            ),
            "14.035" => array(
                "Name" => "Heat capacity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J/K"
            ),
            "14.036" => array(
                "Name" => "Heat flow rate",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "W"
            ),
            "14.037" => array(
                "Name" => "Heat quantity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J"
            ),
            "14.038" => array(
                "Name" => "Impedance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Ohm"
            ),
            "14.039" => array(
                "Name" => "Length",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m"
            ),
            "14.040" => array(
                "Name" => "Light quantity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J"
            ),
            "14.041" => array(
                "Name" => "Luminance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "cd/m²"
            ),
            "14.042" => array(
                "Name" => "Luminous flux",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "lm"
            ),
            "14.043" => array(
                "Name" => "Luminous intensity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "cd"
            ),
            "14.044" => array(
                "Name" => "Magnetic field strengh",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A/m"
            ),
            "14.045" => array(
                "Name" => "Magnetic flux",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Wb"
            ),
            "14.046" => array(
                "Name" => "Magnetic flux density",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "T"
            ),
            "14.047" => array(
                "Name" => "Magnetic moment",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A.m²"
            ),
            "14.048" => array(
                "Name" => "Magnetic polarization",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "T"
            ),
            "14.049" => array(
                "Name" => "Magnetization",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A/m"
            ),
            "14.050" => array(
                "Name" => "Magnetomotive force",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "A"
            ),
            "14.051" => array(
                "Name" => "Mass",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "kg"
            ),
            "14.052" => array(
                "Name" => "Mass flux",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "kg/s"
            ),
            "14.053" => array(
                "Name" => "Momentum",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "N/s"
            ),
            "14.054" => array(
                "Name" => "Phase angle, radiant",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "rad"
            ),
            "14.055" => array(
                "Name" => "Phase angle, degree",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "°"
            ),
            "14.056" => array(
                "Name" => "Power",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "W"
            ),
            "14.057" => array(
                "Name" => "Power factor",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "cos phi"
            ),
            "14.058" => array(
                "Name" => "Pressure",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Pa"
            ),
            "14.059" => array(
                "Name" => "Reactance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Ohm"
            ),
            "14.060" => array(
                "Name" => "Resistance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Ohm"
            ),
            "14.061" => array(
                "Name" => "Resistivity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Ohm.m"
            ),
            "14.062" => array(
                "Name" => "Self inductance",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "H"
            ),
            "14.063" => array(
                "Name" => "Solid angle",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "sr"
            ),
            "14.064" => array(
                "Name" => "Sound intensity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "W/m²"
            ),
            "14.065" => array(
                "Name" => "Speed",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m/s"
            ),
            "14.066" => array(
                "Name" => "Stress",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "Pa"
            ),
            "14.067" => array(
                "Name" => "Surface tension",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "N/m"
            ),
            "14.068" => array(
                "Name" => "Temperature, common",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "°C"
            ),
            "14.069" => array(
                "Name" => "Temperature, absolute",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "K"
            ),
            "14.070" => array(
                "Name" => "Temperature difference",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "K"
            ),
            "14.071" => array(
                "Name" => "Thermal capacity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J/K"
            ),
            "14.072" => array(
                "Name" => "Thermal conductivity",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "W/m/K"
            ),
            "14.073" => array(
                "Name" => "Thermoelectric power",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "V/K"
            ),
            "14.074" => array(
                "Name" => "Time",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "s"
            ),
            "14.075" => array(
                "Name" => "Torque",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "N.m"
            ),
            "14.076" => array(
                "Name" => "Volume",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m³"
            ),
            "14.077" => array(
                "Name" => "Volume flux",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "m³/s"
            ),
            "14.078" => array(
                "Name" => "Weight",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "N"
            ),
            "14.079" => array(
                "Name" => "Work",
                "Valeurs" => array(
                    -3.4028234663852886e+38,
                    3.4028234663852886e+38
                ),
                "Unite" => "J"
            )
        ),
        "String" => array(
            "16.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    5192296858534827628530496329220095
                ),
                "Unite" => ""
            ),
            "16.000" => array(
                "Name" => "String",
                "Valeurs" => array( /*14 * (0,), 14 * (127,)*/ ),
                "Unite" => ""
            ),
            "16.001" => array(
                "Name" => "String",
                "Valeurs" => array( /*14 * (0,), 14 * (255,)*/ ),
                "Unite" => ""
            )
        ),
        "Scene" => array(
            "17.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    255
                ),
                "Unite" => ""
            ),
            "17.001" => array(
                "Name" => "Scene",
                "Valeurs" => array(
                    array(
                        0,
                        0
                    ),
                    array(
                        1,
                        63
                    )
                ),
                "Unite" => ""
            )
        ),
        "DateTime" => array(
            "19.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(),
                "Unite" => ""
            ),
            "19.001" => array(
                "Name" => "DateTime",
                "Valeurs" => array(),
                "Unite" => ""
            )
        ),
        "8BitEncAbsValue" => array(
            "20.xxx" => array(
                "Name" => "Generic",
                "Valeurs" => array(
                    0,
                    255
                ),
                "Unite" => ""
            ),
            "20.003" => array(
                "Name" => "Occupancy mode",
                "Valeurs" => array(
                    "occupied",
                    "standby",
                    "not occupied"
                ),
                "Unite" => ""
            ),
            "20.102" => array(
                "Name" => "Heating mode",
                "Valeurs" => array(
                    "Auto",
                    "Comfort",
                    "Standby",
                    "Night",
                    "Frost"
                ),
                "Unite" => ""
            ),
            "20.102_2" => array(
                "Name" => "MDT Heating mode",
                "Valeurs" => array(
                    "Auto",
                    "Comfort",
                    "Standby",
                    "Night",
                    "Frost"
                ),
                "Unite" => ""
            ),
            "20.105" => array(
                "Name" => "Heating controle mode",
                "Valeurs" => array(
                    "Auto",
                    "Heat",
                    "Morning Warmup",
                    "Cool",
                    "Night Purge",
                    "Precool",
                    "Off",
                    "Test",
                    "Emergency Heat",
                    "Fan only",
                    "Free Cool",
                    "Ice"
                ),
                "Unite" => ""
            )
        )
    );
}
?>