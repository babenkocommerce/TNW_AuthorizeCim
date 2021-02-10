<?php
/**
 * Copyright © 2016 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

namespace TNW\AuthorizeCim\Setup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 *
 * @package TNW\AuthorizeCim\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.4') < 0) {
            $this->version_2_1_4($context, $setup);
        }

        $setup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    protected function version_2_1_4(
        ModuleContextInterface $context,
        ModuleDataSetupInterface $setup
    ) {
        $configTable = $setup->getTable('core_config_data');
        $select = $setup->getConnection()->select()
            ->from($configTable)
            ->where("path = 'tnw_module-authorizenetcim/survey/start_date'");
        $result = $setup->getConnection()->fetchAll($select);
        if ($result) {
            foreach ($result as $configNode) {
                $setup->getConnection()->update(
                    $configTable,
                    [
                        'value' => date_create()->modify('+7 day')->getTimestamp()
                    ],
                    ['config_id = ?' => $configNode['config_id']]
                );
            }
        } else {
            $setup->getConnection()->insert(
                $configTable,
                [
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => 'tnw_module-authorizenetcim/survey/start_date',
                    'value' => date_create()->modify('+7 day')->getTimestamp()
                ]
            );
        }
    }
}
