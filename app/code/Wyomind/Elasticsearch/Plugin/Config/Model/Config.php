<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Elasticsearch\Plugin\Config\Model;

/**
 * Add log lines when modifying the license group of any extension
 */
class Config
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager = null;

    public function __construct(
    \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Check the value of the configuration before saving them
     * @param type $subject
     */
    public function beforeSave($subject)
    {
        $groups = $subject->getGroups();
        if ($groups != null) {
            foreach ($groups as $groupId => $groupData) {
                $groupPath = $subject->getSection() . '/' . $groupId;
                if ($groupPath == "catalog/search") {
                    if (isset($groupData['groups']['elasticsearch']) && isset($groupData["fields"]["engine"]) && isset($groupData["fields"]["engine"]["value"]) && $groupData["fields"]["engine"]["value"] == "elasticsearch") {
                        // automatically retrieve the ES server version
                        $groups[$groupId]['groups']["elasticsearch"]['fields']['compatibility']["value"] = 6;
                        if (isset($groupData['groups']['elasticsearch']['fields']['servers']['value'])) {
                            $hosts = explode(',', $groupData['groups']['elasticsearch']['fields']['servers']['value']);
                            foreach ($hosts as $host) {
                                $test = \Elasticsearch\ClientBuilder::create()->setHosts([$host])->build();
                                try {
                                    $info = $test->info([ "client" => ["verify" => false, "connect_timeout" => 5]]);
                                    $version = explode(".", $info['version']['number']);
                                    $version = array_shift($version);
                                    if (in_array($version, [2, 5, 6])) {
                                        $groups[$groupId]['groups']["elasticsearch"]['fields']['version']["value"] = $info['version']['number'];
                                        $groups[$groupId]['groups']["elasticsearch"]['fields']['compatibility']["value"] = $version;
                                        $subject->setGroups($groups);
                                        $this->messageManager->addSuccess(__("Elasticsearch server version found: ") . $info['version']['number']);
                                    } else {
                                        $groups[$groupId]['groups']["elasticsearch"]['fields']['version']["value"] = $info['version']['number'];
                                        $groups[$groupId]['groups']["elasticsearch"]['fields']['compatibility']["value"] = 6;
                                        $subject->setGroups($groups);
                                        $this->messageManager->addError(__("Elasticsearch server version found not compatible: ") . $info['version']['number']);
                                    }
                                } catch (\Exception $e) {
                                    $groups[$groupId]['groups']["elasticsearch"]['fields']['version']["value"] = "";
                                    $groups[$groupId]['groups']["elasticsearch"]['fields']['compatibility']["value"] = 6;
                                    $subject->setGroups($groups);
                                    $this->messageManager->addError(__("Cannot find the Elasticsearch server version: ") . $e->getMessage());
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
