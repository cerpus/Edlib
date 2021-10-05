<?php


namespace App\Libraries;


class SystemInfo
{

    public function getLoadAverage()
    {
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : ['-', '-', '-'];

        return $loadAvg;
    }

    public function getMemoryUsage()
    {
        $memoryUsage = '-';
        if (function_exists('shell_exec')) {
            try {
                $free = shell_exec('free');
                $free = (string)trim($free);
                $free_arr = explode("\n", $free);
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);
                $memoryUsage = $mem[2] / $mem[1] * 100;
            } catch (\Throwable $t) {
            }
        }

        return $memoryUsage;
    }

    public function getAvailableMemory()
    {
        $availMem = '-';
        if (file_exists('/proc/meminfo')) {
            $fh = fopen('/proc/meminfo', 'r');
            while ($line = fgets($fh)) {
                $pieces = array();
                if (preg_match('/^MemAvailable:\s+(\d+)\skB$/', $line, $pieces)) {
                    $availMem = $pieces[1] / 1024;
                    break;
                }
            }
            fclose($fh);
        }

        return $availMem;
    }

    public function getPhpVersion()
    {
        $phpVersion = '-';
        if (function_exists('phpversion')) {
            $phpVersion = phpversion();
        }

        return $phpVersion;
    }

    public function getUptime()
    {
        $uptime = '-';
        if (file_exists('/proc/uptime')) {
            try {
                $uptimeF = file_get_contents('/proc/uptime');
                $parts = explode(' ', $uptimeF);
                $uptimeS = (int)$parts[0];
                $dateS = new \DateTime('@0');
                $dateT = new \DateTime("@$uptimeS");

                $uptime = $dateS->diff($dateT)->format('%a days, %h hours, %i minutes and %s seconds');
            } catch (\Throwable $t) {
            }
        }

        return $uptime;
    }

    public function getCpuInfo(): array
    {
        $cpuInfo = [
            'physicalCores' => '-',
            'logicalCores' => '-',
            'cpuModel' => '-',
            'bogoMips' => '-',
        ];

        try{
            if (file_exists('/proc/cpuinfo')) {
                $cpuInfoF = file_get_contents('/proc/cpuinfo', 'r');
                if ($cpuInfoF) {
                    $cpuInfoF = str_replace("\t", '', $cpuInfoF);
                    $cpuInfo = [];
                    $cpuInfoParts = explode("\n", $cpuInfoF);
                    $num = 0;
                    foreach ($cpuInfoParts as $line) {
                        $parts = explode(':', $line);
                        if (count($parts) == 1) { // empty
                            continue;
                        }
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if ($key == 'processor') {
                            $num = $value;
                        }
                        $cpuInfo[$num][$key] = $value;
                    }
                    if (count($cpuInfo) > 0) {
                        $logicalCoreCount = count($cpuInfo);
                        if($logicalCoreCount < 1){
                            $logicalCoreCount = 1;
                        }
                        $cpuInfo['physicalCores'] = $cpuInfo[0]['cpu cores'];
                        $cpuInfo['logicalCores'] = $logicalCoreCount;
                        $cpuInfo['cpuModel'] = $cpuInfo[0]['model name'];
                        $cpuInfo['bogoMips'] = $cpuInfo[0]['bogomips'];
                    }
                }
            }
        }
        catch(\Throwable $t){
            // Don't die...
        }

        return $cpuInfo;
    }
}
