<?php

namespace ch1p;

class FastDNS {

    const API_HOST = 'https://fastdns.fv.ee';
    const MAIL_SERVICE_MAIN = 0;
    const MAIL_SERVICE_YANDEX = 2;
    const MAIL_SERVICE_GMAIL = 1;

    protected $jwtToken;
    protected $expire;

    /**
     * @param string $token
     * @throws FastDNSException
     */
    public function auth(string $token) {
        $url = self::API_HOST.'/login_token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authenticate: '.$token
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($body, true);
        if ($code != 200)
            throw new FastDNSException($response['message'], $response['code']);

        $this->jwtToken = $response['token'];
        $this->expire = $response['expire'];
    }

    /**
     * @return mixed
     * @throws FastDNSException
     */
    public function getDomains() {
        return $this->get('/api/domains', []);
    }

    /**
     * @param $id
     * @return mixed
     * @throws FastDNSException
     */
    public function getDomain($id) {
        return $this->get('/api/domains/'.$id);
    }

    /**
     * @param $name
     * @return mixed
     * @throws FastDNSException
     */
    public function getDomainByName($name) {
        return $this->get('/api/domains/'.$name.'/name');
    }

    /**
     * @param string $name
     * @param string $ip
     * @param int $mail_service
     * @return mixed
     * @throws FastDNSException
     */
    public function createDomain(string $name, string $ip, int $mail_service = self::MAIL_SERVICE_MAIN) {
        return $this->post('/api/domains', [
            'name' => $name,
            'ip' => $ip,
            'mail_service' => $mail_service
        ]);
    }

    /**
     * No idea what this does and what "required" means
     *
     * @param $domain_id
     * @param int $required
     * @return mixed
     * @throws FastDNSException
     */
    public function updateDomain($domain_id, int $required) {
        return $this->put('/api/domains/'.$domain_id, [
            'required' => $required
        ]);
    }

    /**
     * @param int $domain_id
     * @return mixed
     * @throws FastDNSException
     */
    public function deleteDomain(int $domain_id) {
        return $this->delete('/api/domains/'.$domain_id);
    }

    /**
     * @param int $domain_id
     * @return mixed
     * @throws FastDNSException
     */
    public function getRecords(int $domain_id) {
        return $this->get('/api/domains/'.$domain_id.'/records');
    }

    /**
     * @param int $domain_id
     * @param string $name
     * @param string $type
     * @param string $content
     * @param int $ttl
     * @param string $tag
     * @param int $flag
     * @param int $priority
     * @param int $weight
     * @param int $port
     * @return mixed
     * @throws FastDNSException
     */
    public function createRecord(
            int $domain_id,
            string $name,
            string $type,
            string $content,
            int $ttl,
            string $tag = '',
            int $flag = 0,
            int $priority = 5,
            int $weight = 0,
            int $port = 0) {
        return $this->post('/api/domains/'.$domain_id.'/records', [
            'name' => $name,
            'type' => $type,
            'content' => $content,
            'ttl' => $ttl,
            'tag' => $tag,
            'flag' => $flag,
            'priority' => $priority,
            'weight' => $weight,
            'port' => $port
        ]);
    }

    /**
     * @param int $domain_id
     * @param string $record_id
     * @return mixed
     * @throws FastDNSException
     */
    public function getRecord(int $domain_id, string $record_id) {
        return $this->get('/api/domains/'.$domain_id.'/records/'.$record_id);
    }

    /**
     * @param int $domain_id
     * @param string $record_id
     * @param string $name
     * @param string $type
     * @param string $content
     * @param int $ttl
     * @param string $tag
     * @param int $flag
     * @param int $priority
     * @param int $weight
     * @param int $port
     * @return mixed
     * @throws FastDNSException
     */
    public function updateRecord(
        int $domain_id,
        string $record_id,
        string $name,
        string $type,
        string $content,
        int $ttl,
        string $tag = '',
        int $flag = 0,
        int $priority = 5,
        int $weight = 0,
        int $port = 0) {

        // если здесь передавать все поля, то сервер возвращает ошибку "запись уже существует",
        return $this->put('/api/domains/'.$domain_id.'/records/'.$record_id, [
            // 'type' => $type,
            'content' => $content,
            'name' => $name,
            // 'ttl' => $ttl,
            // 'tag' => $tag,
            // 'flag' => $flag,
            // 'priority' => $priority,
            // 'weight' => $weight,
            // 'port' => $port
        ]);
    }

    /**
     * @param int $domain_id
     * @param string $record_id
     * @throws FastDNSException
     */
    public function deleteRecord(int $domain_id, string $record_id) {
        return $this->delete('/api/domains/'.$domain_id.'/records/'.$record_id);
    }

    /**
     * @return mixed
     * @throws FastDNSException
     */
    public function getUserInfo() {
        return $this->get('/api/me');
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return mixed
     * @throws FastDNSException
     */
    protected function get(string $endpoint, array $params = []) {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return mixed
     * @throws FastDNSException
     */
    protected function post(string $endpoint, array $params = []) {
        return $this->request('POST', $endpoint, $params);
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return mixed
     * @throws FastDNSException
     */
    protected function put(string $endpoint, array $params = []) {
        return $this->request('PUT', $endpoint, $params);
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return mixed
     * @throws FastDNSException
     */
    protected function delete(string $endpoint, array $params = []) {
        return $this->request('DELETE', $endpoint, $params);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @return mixed
     * @throws FastDNSException
     */
    protected function request(string $method, string $endpoint, array $params = []) {
        if (!$this->jwtToken)
            throw new FastDNSException(__METHOD__.': JWT token is null, forgot to authorize?');

        $url = self::API_HOST.$endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($params)) {
            if ($method === 'POST' || $method == 'PUT') {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            } else if ($method === 'GET') { // Probably never used
                $url .= '?'.http_build_query($params);
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer '.$this->jwtToken
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($body, true);
        if ($code >= 400) {
            if (!empty($response['code']) && !empty($response['message'])) {
                $message = $response['message'];
                $code = $response['code'];
            } else {
                $message = $response['errors']['name'] ?? $body;
            }
            throw new FastDNSException($message, $code);
        }

        return $response;
    }

}