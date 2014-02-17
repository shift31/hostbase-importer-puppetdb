# Hostbase PuppetDB Importer

## Installation

1. Download/clone this whole repository or install with `composer create-project shift31/hostbase-importer-puppetdb`
2. Run `composer install` from the project root

## Configuration

From the project root, create a config.ini:

```
puppetDbBaseUrl = "http://your.puppetdb.server:8080"
hostbaseUrl = "http://your.hostbase.server"
dataCenterFact = "a_custom_fact_denoting_datacenter"
environmentFact = "a_custom_fact_denoting_environment"
factsToFilterRegex = "/id|ssh|swap|_lo|last_run|memoryfree|path|swapfree|uptime|uniqueid|clientcert/"
```