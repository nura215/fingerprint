<?php

namespace App\Services;

use App\Models\Device;
use RuntimeException;

class SolutionX606SoapService
{
    public function uploadUser(Device $device, string $fingerprintId, string $name): array
    {
        $body = '<SetUserInfo>'.
            '<ArgComKey Xsi:type="xsd:integer">'.$this->xml($this->commKey($device)).'</ArgComKey>'.
            '<Arg>'.
            '<PIN>'.$this->xml($fingerprintId).'</PIN>'.
            '<Name>'.$this->xml($name).'</Name>'.
            '</Arg>'.
            '</SetUserInfo>';

        $response = $this->request($device, $body);
        $message = $this->between($response, '<Information>', '</Information>') ?: trim($response);

        return [
            'success' => stripos($message, 'success') !== false || stripos($message, 'successfully') !== false,
            'message' => $message,
            'raw' => $response,
        ];
    }

    public function getAttendanceLogs(Device $device): array
    {
        $body = '<GetAttLog>'.
            '<ArgComKey xsi:type="xsd:integer">'.$this->xml($this->commKey($device)).'</ArgComKey>'.
            '<Arg><PIN xsi:type="xsd:integer">All</PIN></Arg>'.
            '</GetAttLog>';

        $response = $this->request($device, $body, 10);
        $payload = $this->between($response, '<GetAttLogResponse>', '</GetAttLogResponse>');

        if ($payload === '') {
            return [];
        }

        preg_match_all('/<Row>(.*?)<\/Row>/is', $payload, $matches);

        return collect($matches[1] ?? [])
            ->map(function (string $row) {
                return [
                    'fingerprint_id' => $this->between($row, '<PIN>', '</PIN>'),
                    'scan_time' => $this->between($row, '<DateTime>', '</DateTime>'),
                    'verified' => $this->between($row, '<Verified>', '</Verified>'),
                    'status' => $this->between($row, '<Status>', '</Status>'),
                    'raw' => $row,
                ];
            })
            ->filter(fn (array $row) => $row['fingerprint_id'] !== '' && $row['scan_time'] !== '')
            ->values()
            ->all();
    }

    public function deleteUser(Device $device, string $fingerprintId): array
    {
        $body = '<DeleteUser><ArgComKey Xsi:type="xsd:integer">'.$this->xml($this->commKey($device)).'</ArgComKey><Arg><PIN>'.$this->xml($fingerprintId).'</PIN></Arg></DeleteUser>';
        $response = $this->request($device, $body);
        $message = $this->between($response, '<Information>', '</Information>') ?: trim($response);

        return [
            'success' => stripos($message, 'success') !== false || stripos($message, 'successfully') !== false,
            'message' => $message,
            'raw' => $response,
        ];
    }

    public function syncTime(Device $device): array
    {
        $body = '<SetDate><ArgComKey Xsi:type="xsd:integer">'.$this->xml($this->commKey($device)).'</ArgComKey><Arg><Date>'.now()->format('Y-m-d').'</Date><Time>'.now()->format('H:i:s').'</Time></Arg></SetDate>';
        $response = $this->request($device, $body);
        $message = $this->between($response, '<Information>', '</Information>') ?: trim($response);

        return [
            'success' => stripos($message, 'success') !== false || stripos($message, 'successfully') !== false,
            'message' => $message,
            'raw' => $response,
        ];
    }

    private function request(Device $device, string $body, int $timeout = 5): string
    {
        if (blank($device->ip_address)) {
            throw new RuntimeException('IP address perangkat belum diisi.');
        }

        $port = $device->port ?: 80;
        $connection = @fsockopen($device->ip_address, $port, $errno, $errstr, $timeout);

        if (! $connection) {
            throw new RuntimeException("Koneksi ke {$device->ip_address}:{$port} gagal. {$errno} {$errstr}");
        }

        stream_set_timeout($connection, $timeout);

        $newLine = "\r\n";
        fwrite($connection, 'POST /iWsService HTTP/1.0'.$newLine);
        fwrite($connection, 'Content-Type: text/xml'.$newLine);
        fwrite($connection, 'Content-Length: '.strlen($body).$newLine.$newLine);
        fwrite($connection, $body.$newLine);

        $response = '';
        while (! feof($connection)) {
            $response .= fgets($connection, 2048);
        }

        fclose($connection);

        return $response;
    }

    private function commKey(Device $device): string
    {
        return filled($device->comm_key) ? (string) $device->comm_key : '0';
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function between(string $value, string $start, string $end): string
    {
        $startPosition = stripos($value, $start);

        if ($startPosition === false) {
            return '';
        }

        $startPosition += strlen($start);
        $endPosition = stripos($value, $end, $startPosition);

        if ($endPosition === false) {
            return '';
        }

        return trim(substr($value, $startPosition, $endPosition - $startPosition));
    }
}
