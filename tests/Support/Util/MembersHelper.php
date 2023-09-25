<?php

namespace DachcomBundle\Test\Support\Util;

use MembersBundle\Tool\Install;
use Pimcore\Model\Document;

class MembersHelper
{
    public const DEFAULT_FEU_USERNAME = 'chuck';
    public const DEFAULT_FEU_EMAIL = 'test@universe.org';
    public const DEFAULT_FEU_PASSWORD = 'default-password';
    public const DEFAULT_FEG_NAME = 'Default Group';

    public static function assertMailSender(): void
    {
        // we need to set a valid sender
        $emailListing = new Document\Listing();
        $emailListing->setCondition('type = ?', ['email']);
        /** @var Document\Email $email */
        foreach ($emailListing->getDocuments() as $email) {
            if (empty($email->getFrom())) {
                $email->setFrom('no-reply@localhost');
                $email->save();
            }
        }
    }

    public static function reCreateMembersStructure(): void
    {
        $installer = \Pimcore::getContainer()->get(Install::class);
        $installer->install();
    }
}
