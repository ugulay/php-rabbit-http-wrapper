<?php

/**
 * RABBITMQ HTTP API
 * @author Uğur Gülay <ugur.gulay@tsoft.com.tr>
 * __construct 'da bulunan config düzenlenerek rabbit üzerinden HTTP ye gidebilirsiniz.
 * döküman için : https://pulse.mozilla.org/api/
 * 
 * Bir çok işlem bir VHOST  'a ihtiyaç duymaktadır. Önce VHOST ile bu listeyi çekip kullanmak istediğiniz VHOST ismine karar vermelisiniz.
 * 
 */

namespace Library;

class RabbitApi {

    protected $config;
    protected $result;
    protected $virtualHost = '/';
    protected $path = '';

    function __construct($configParameters) {
        $this->config = [
            'host' => $configParameters['rabbitHost'] . ':'. $configParameters['rabbitPort'] .'/api/',
            'user' => $configParameters['rabbitUser'],
            'pass' => $configParameters['rabbitPass']
        ];
    }

    /**
     * Tüm istekler bu fonksiyon üzerinden yürümektedir.
     * @param type $path
     * @param type $method
     * @param type $data
     * @return type
     */
    function httpRequest($path, $method = 'GET', $data = array()) {

        $data_ = json_encode($data);

        $ch = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['user'] . ":" . $this->config['pass']);
        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . mb_strlen($data_)
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->config['host'] . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method != 'GET' || $method != 'HEAD') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, false);
        }

        if (count($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_);
        }

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    function setVirtualHost($vhost = '') {
        $this->virtualHost = (string) $vhost;
        return $this;
    }

    function getVirtualHost() {
        return (string) $this->virtualHost == '/' ? '%2f' : $this->virtualHost;
    }

    /**
     * Sistem hakkında genel bilgiler verir.
     * @param GET
     * @return $this
     */
    function getInfo() {
        $path = 'overview';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Çalışan Node 'ların bilgilerini döndürür.
     * @return $this
     */
    function getNodes() {
        $path = 'nodes';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Tek bir Noda ait bilgileri döndürür
     * @param  $name
     * @return $this
     */
    function getNode($name) {
        $path = 'nodes/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Bağlı olan istemcilerin bilgilerini çevirir.
     * @return $this
     */
    function getConnections() {
        $path = 'connections';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Tek bir bağlantıya ait bilgileri çevirir.
     * @param  $name
     * @return $this
     */
    function getConnection($name) {
        $path = 'connections/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Belirtilen bağlantıyı siler.
     * @param  $name
     * @return $this
     */
    function deleteConnection($name) {
        $path = $this->result = $this->httpRequest('connections/' . $name, 'DELETE');
        return $this;
    }

    /**
     * Tüm kanallara ait bilgileri getirir.
     * @return $this
     */
    function getChannels() {
        $path = 'channels';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Tek bir kanala ait bilgileri getirir.
     * @param  $name
     * @return $this
     */
    function getChannel($name) {
        $path = 'channels/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Tüm Exchanges bilgilerini döndürür
     * @return $this
     */
    function getExchanges() {
        $path = 'exchanges';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Sanallaştırılmış olan Exchanges bilgilerini döndürür.
     * @param  $vhost
     * @return $this
     */
    function getExchangesVHosts() {
        $path = 'exchanges/' . $this->getVirtualHost();
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Sanallaştırılmış olan Exchange deki belirtilen nesneyi döndürür.
     * @param  $name
     * @return $this
     */
    function getExchangesVHost($name) {
        $path = 'exchanges/' . $this->getVirtualHost() . '/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Sanallaştırılmış olan Exchange deki belirtilen nesneyi siler.
     * @param  $name
     * @return $this
     */
    function deleteExchangesVHost($name) {
        $path = 'exchanges/' . $this->getVirtualHost() . '/' . $name;
        $this->result = $this->httpRequest(($path), 'DELETE');
        return $this;
    }

    /**
     * Kuyruk listesini getirir
     * @return $this
     */
    function getQueues() {
        $path = 'queues';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Kuyruk listesinden istediğiniz anahtara sahip verileri getirir
     * @param type $key
     * @return $this
     */
    function getQueuesList($key = 'name') {

        $get = $this->httpRequest('queues');

        $this->result = $get;

        if ($key !== false) {

            $ready = [];

            foreach (json_decode($get, true) as $val) {
                if ($key !== false && isset($val[$key])) {
                    $ready[] = $val[$key];
                } else {
                    $ready[] = $val;
                }
            }

            $this->result = json_encode($ready);
        }

        return $this;
    }

    /**
     * Sanallaştırılmış olan kuyrukları getirir
     * @return $this
     */
    function getQueuesVHosts() {
        $path = 'queues/' . $this->getVirtualHost();
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * Sanallaştırılmış olan bir kuyruktaki bilgsiyi getirir
     * @param  $name
     * @return $this
     */
    function getQueuesVHost($name) {
        $path = 'queues/' . $this->getVirtualHost() . '/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * İsmi belirtilen sanallaştırılmış bir kuyruğu siler
     * @param  $name
     * @return $this
     */
    function deleteQueuesVHost($name) {
        $path = 'queues/' . $this->getVirtualHost() . '/' . $name;
        $this->result = $this->httpRequest(($path), 'DELETE');
        return $this;
    }

    /**
     * ismi belirtilen kuyruğu boşaltır.
     * @param array $name
     * @return $this
     */
    function purgeQueue($name) {
        $path = 'queues/' . $this->getVirtualHost() . '/' . $name . '/contents';
        $this->result = $this->httpRequest(($path), 'DELETE');
        return $this;
    }

    /**
     * İsmi belirtilen sanallaştırılmış bir kuyruğu siler
     * @param  $name Kuyruk Adı
     * @param $data ["auto_delete" => false,"durable" => true,"arguments" => [],"node" => "rabbit@smacmullen"]
     * @return $this
     */
    function addQueueVHost($name, $data) {
        $path = 'queues/' . $this->getVirtualHost() . '/' . $name;
        $this->result = $this->httpRequest(($path), 'PUT', $data);
        return $this;
    }

    /**
     * İsmi belirtilen sanallaştırılmış kuyruktaki mesajları getirir.

     * @param  $name Kuyruk Adı
     * @param array $options Seçenekler
     * @return $this
     */
    function getQueueMessages($name, $options = array()) {
        $opt = [
            'count' => 1,
            'requeue' => true,
            'encoding' => 'auto'
        ];
        $opt = array_replace_recursive($opt, $options);
        $path = 'queues/' . $this->getVirtualHost() . '/' . $name . '/get';
        $this->result = $this->httpRequest(($path), 'GET', $opt);
        return $this;
    }

    /**
     * Sanallaştırmaların listesini getirir.
     * @return $this
     */
    function getVHosts() {
        $path = 'vhosts';
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * İsmi belitrilen sanallaştırmaya ait bilgileri getirir.
     * @return $this
     */
    function getVHost() {
        $path = 'vhosts/' . $this->getVirtualHost();
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * İsmi belitrilen sanallaştırmayı ekler.
     * $param $data ["tracing" => true]
     * @return $this
     */
    function addVHost($data = array()) {
        $path = 'vhosts/' . $this->getVirtualHost();
        $data['tracing'] = true;
        $this->result = $this->httpRequest(($path), 'PUT', $data);
        return $this;
    }

    /**
     * İsmi belitrilen sanallaştırmayı siler.
     * @return $this
     */
    function deleteVHost() {
        $path = 'vhosts/' . $this->getVirtualHost();
        $this->result = $this->httpRequest(($path), 'DELETE');
        return $this;
    }

    /**
     * Tüm kullanıcıları getirir.
     * @return $this
     */
    function getUsers() {
        $path = 'vhosts/' . $this->getVirtualHost();
        $this->result = $this->httpRequest(($path), 'DELETE');
        return $this;
    }

    /**
     * İsmi belirtilen kullanıcının bilgilerini getirir.
     * @param type $name
     * @return $this
     */
    function getUser($name = false) {
        $path = 'users/' . $name;
        $this->result = $this->httpRequest(($path));
        return $this;
    }

    /**
     * İsmi belirtilen kullanıcı $body değişkenindeki bilgiler ile oluşturur.
     * @param type $name
     * @param type $body 
     * örnek $body içeriği: ["password"=>"secret","tags"=>"administrator"] 
     *                      ["password_hash" => "2lmoth8l4H0DViLaK9Fxi6l9ds8=", "tags"=>"administrator"]
     *                      kullanılabilir taglar :  "administrator", "monitoring", "management"
     * @return $this
     */
    function addUser($name, $body = array()) {
        $path = 'users/' . $name;
        $this->result = $this->httpRequest($path, 'PUT', $body);
        return $this;
    }

    /**
     * İsmi belirtilen kullanıcıyı siler.
     * @param type $name
     * @return $this
     */
    function deleteUser($name) {
        $path = 'users/' . $name;
        $this->result = $this->httpRequest($path, 'DELETE');
        return $this;
    }

    function getUserPermissions($name) {
        $path = 'users/' . $name . '/permissions';
        $this->result = $this->httpRequest($path, 'GET');
        return $this;
    }

    /**
     * Sonucu getiren metod.
     * @param type $key 
     * @param type $decode
     * @return type
     */
    function result($key = false, $decode = true) {
        $res = $this->result;
        $this->result = null;

        if ($decode == true) {
            $res = json_decode($res, true);
        }

        if ($key !== false && isset($res[$key])) {
            return $res[$key];
        }

        return $res;
    }

}
