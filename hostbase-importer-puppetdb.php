<?php

require __DIR__ . '/vendor/autoload.php';

use Shift31\HostbaseClient;
use Httpful\Request;
use Httpful\Response;

$config = parse_ini_file(__DIR__ . '/config.ini');
$puppetDbBaseUrl = $config['puppetDbBaseUrl'];
$datacenterFact = isset($config['datacenterFact']) ? $config['datacenterFact'] : null;
$environmentFact = isset($config['environmentFact']) ? $config['environmentFact'] : null;

$HbClient = new HostbaseClient($config['hostbaseUrl']);

// get nodes
$response = Request::get("$puppetDbBaseUrl/v2/nodes")
	->sendsAndExpects('application/json')
	->send();

if ($response instanceof Response) {
	$nodes = $response->body;

	foreach ($nodes as $node) {

		$data = array();
		$fqdn = $node->name;
		$data['fqdn'] = $fqdn;

		// get facts
		$response = Request::get("$puppetDbBaseUrl/v2/nodes/$node->name/facts")
			->sendsAndExpects('application/json')
			->send();

		if ($response instanceof Response) {
			$facts = $response->body;

			foreach ($facts as $fact) {
				if (preg_match('/id|ssh|swap|_lo|last_run|memoryfree|path|swapfree|uptime|uniqueid|clientcert/', $fact->name)) continue;
				if ($fact->name == 'ps') continue;

				if (preg_match('/count|size|mtu/', $fact->name)) {
					$value = (int) $fact->value;
				} else {
					$value = $fact->value;
				}

				if ($fact->value == 'false') {
					$value = false;
				}

				if ($fact->value == 'true') {
					$value = true;
				}

				$data[$fact->name] = $value;

				// make sure 'datacenter' field is populated
				if ($fact->name == $datacenterFact) {
					$data['datacenter'] = $value;
				}
				// make sure 'environment' field is populated
				if ($fact->name == $environmentFact) {
					$data['environment'] = $value;
				}
			}
		}

		echo "Importing $fqdn...";

		try {
			// add
			echo "adding...\n";
			$HbClient->store($data);
		} catch (Exception $e) {

			echo $e->getMessage() . PHP_EOL;

			try {
				// update
				echo "updating...\n";
				$HbClient->update($fqdn, $data);
			} catch (Exception $e) {
				echo $e->getMessage() . PHP_EOL;
			}
		}
	}
}
