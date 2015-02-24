<?php

class VPanel {
	private $apiurl;
	private $sessionid;
	private $authhash;

	function __construct($apiurl) {
		$this->apiurl = $apiurl;
	}

	function startSession($user, $apikey) {
		$req1 = json_decode(file_get_contents($this->apiurl . 'api/startsession.php?username=' . urlencode($user)));
		$this->sessionid = $req1->sessionid;
		$this->apikey = $apikey;
		$this->authhash = hash_hmac("md5", $req1->challenge, $this->apikey);
	}

	private function callApi($api, $data) {
		$data["sessionid"] = $this->sessionid;
		$data["authhash"] = $this->authhash;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiurl . 'api/' . $api);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, build_curl_array($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);

		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			throw new Exception('Verwaltungs-API liefert Fehlercode ' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . ' - ' . $ret);
		}
		$data = json_decode($ret);
		if (isset($data->result->failed)) {
			throw new Exception('Verwaltungs-API liefert Fehlercode ' . $data->result->failed);
	       	}
		$this->authhash = hash_hmac("md5", $data->challenge, $this->apikey);
		return $data->result;
	}

	function uploadDocument($dokumenttemplateid, $filename, $data = array()) {
		$data["dokumenttemplateid"] = $dokumenttemplateid;
		$data["file"] = "@" . $filename . ";type=application/pdf";

		$this->callApi('createdokument.php', $data);
		return true;
	}

	function getMitglied($mitgliedid) {
		return $this->callApi('mitglied.php', array("mitgliedid" => $mitgliedid))->mitglied;
	}

	function modifyMitglied($mitgliedid, $changes) {
		return $this->callApi('modifymitglied.php', array("mitgliedid" => $mitgliedid, "changes" => $changes));
	}

	function addMitgliedBeitragBuchung($mitgliedid, $beitragid, $timestamp, $gliederungid, $vermerk, $hoehe, $mailtemplateid = null) {
		$query = array("sessionid" => $this->sessionid, "authhash" => $this->authhash, "mitgliedid" => $mitgliedid, "beitragid" => $beitragid, "timestamp" => $timestamp, "gliederungid" => $gliederungid, "vermerk" => $vermerk, "hoehe" => $hoehe);
		if ($mailtemplateid !== null) {
			$query["mailtemplateid"] = $mailtemplateid;
		}
		return $this->callApi('mitgliedbeitragbuchen.php', $query);
	}
}

function build_curl_array($arr, $prefix = "", &$inarray = array()) {
	foreach ($arr as $k => $v) {
		$k = ($prefix == "" ? $k : $prefix . "[" . urlencode($k) . "]");
		if (is_array($v)) {
			build_curl_array($v, $k, $inarray);
		} else {
			$inarray[$k] = $v;
		}
	}
	return $inarray;
}
