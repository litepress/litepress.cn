<?php

use Premmerce\UrlManager\Admin\BundleAndSave;

if (! defined('ABSPATH')) {
    exit;
}

$bundlesFAQs = [
    [
        'question' => __('How does the domain limit work?', 'premmerce-url-manager'),
        'answer'   => __('Once you choose a plan, you get access to the products described in the package and a specific number of domain licenses. You can activate any of the premium products (themes or plugins) on the domains where you have an active license.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('What will happen if I if I don’t renew my license in a year?', 'premmerce-url-manager'),
        'answer'   => __('Your domain license will become invalid in 12 months period. The product will continue to work.However, after your license expires, the access to security updates, support requests, and new features is discontinued until the new license is activated. For lifetime membership this is not the case, only for the annual plans.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('How often do I have to pay?', 'premmerce-url-manager'),
        'answer'   => __('With the annual membership, you will have to renew after the first year if you want to continue receiving security updates and support. The product will continue to work even if you cancel immediately, but you won’t get the updates and support after the license expires. You are free to upgrade/cancel anytime. Lifetime is a one-time payment, and as long as the product is maintained, you will get support and security updates.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('Do you offer refunds?', 'premmerce-url-manager'),
        'answer'   => __('Yes, if you have an issue that cannot be resolved via our support service within 14 days from the purchase date, you will be offered a full refund.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('Will my subscription renew automatically?', 'premmerce-url-manager'),
        'answer'   => __('Yes, your subscription will renew automatically every year, until its cancellation.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('Can I use my licence in a development environment?', 'premmerce-url-manager'),
        'answer'   => __('Yes. Most development environments will not count towards your site limit.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('How do I download the plugins?', 'premmerce-url-manager'),
        'answer'   => __('Once you place your order, you\'ll be sent an email containing the download links to all the purchased plugins and themes.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('How much money am I saving with a Premmerce Suite bundle?', 'premmerce-url-manager'),
        'answer'   => __('You will save at least 70% when you buy a bundle. This percentage is even higher if you opt to pay annually.', 'premmerce-url-manager'),
    ],
    [
        'question' => __('Are there any hidden fees and what do we have to pay for after the project has been delivered?', 'premmerce-url-manager'),
        'answer'   => __('There are no hidden or recurring charges to be paid after a project delivery. However, you need a hosting platform and a domain name for your website which are usually paid on a regular basis. You can buy them from our company or elsewhere. The most important thing is that your hosting platform must adhere to our software minimal requirements.', 'premmerce-url-manager'),
    ],
];
?>

<div class="c-section">
    <div class="c-section__container">
        <div class="c-section__title"><span style="font-weight: 400;">Frequently Asked Questions</span></div>
        <div class="c-section__desc"><span style="font-weight: 400;">
                <?php _e('If you have any further questions, don’t hesitate to ask us via the online chat or by e-mail', 'premmerce-url-manager'); ?>
            </span>
            <a href="mailto:info@premmerce.com">
                <span style="font-weight: 400;">
                    info@premmerce.com
                </span>
            </a>
        </div>
        <div class="c-section__content">
            <?php
                $rowCount  = 0;
                $numOfCols = 2;
                foreach ($bundlesFAQs as $faq) :
                if ($rowCount % $numOfCols == 0) { ?> <div class="row"> <?php }
                $rowCount++;
            ?>
                <div class="col-sm-6">
                    <div class="pc-qa" data-qa-scope="">
                        <div class="pc-qa__header" data-qa-toggle="">
                            <div class="pc-qa__title">
                                <?php echo $faq['question']; ?>
                            </div>
                            <div class="pc-qa__arrow">
                                <svg class="svg-icon">
                                    <?php BundleAndSave::premmerce_use_svg_symbol($svg, 'arrow-bootom') ?>
                                </svg>
                            </div>
                        </div>
                        <div class="pc-qa__content" data-qa-content="" style="display: none;">
                            <?php echo $faq['answer']; ?>
                        </div>
                    </div>
                </div>
                <?php
                if ( $rowCount % $numOfCols == 0) : ?>
            </div> <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>