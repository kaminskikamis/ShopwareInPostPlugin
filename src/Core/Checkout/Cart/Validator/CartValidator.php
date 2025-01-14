<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start.
 * You can find more information about us on https://bitbag.io and write us an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\ShopwareInPostPlugin\Core\Checkout\Cart\Validator;

use BitBag\ShopwareInPostPlugin\Core\Checkout\Cart\Custom\Error\InvalidPhoneNumberError;
use BitBag\ShopwareInPostPlugin\Core\Checkout\Cart\Custom\Error\InvalidPostCodeError;
use BitBag\ShopwareInPostPlugin\Core\Checkout\Cart\Custom\Error\NullWeightError;
use BitBag\ShopwareInPostPlugin\Core\Checkout\Cart\Custom\Error\StreetSplittingError;
use BitBag\ShopwareInPostPlugin\Factory\ShippingMethodPayloadFactoryInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

final class CartValidator implements CartValidatorInterface
{
    public const STREET_FIRST_REGEX = "/(?<streetName>[[:alnum:].'\- ]+)\s+(?<houseNumber>\d{1,10}((\s)?\w{1,3})?(\/\d{1,10})?)$/";

    public const STREET_WITH_BUILDING_NUMBER_REGEX = "/^([^\d]*[^\d\s]) *(\d.*)$/";

    public const POST_CODE_REGEX = "/^(\d{2})(-\d{3})?$/i";

    public const PHONE_NUMBER_REGEX = "/(?:(?:\+|00)[0-9]{1,3})?(\d{9,12})/";

    public const PHONE_NUMBER_LENGTH = 9;

    public function validate(
        Cart $cart,
        ErrorCollection $errors,
        SalesChannelContext $context
    ): void {
        if (ShippingMethodPayloadFactoryInterface::SHIPPING_KEY !== $this->getTechnicalName($context)) {
            return;
        }

        $delivery = $cart->getDeliveries()->first();

        if (null === $delivery) {
            return;
        }

        $address = $delivery->getLocation()->getAddress();

        if (null === $address) {
            return;
        }

        $postCode = $address->getZipcode();

        $this->checkPostCodeValidity($postCode, $address->getId(), $errors);

        if (!preg_match(self::STREET_WITH_BUILDING_NUMBER_REGEX, $address->getStreet())) {
            $errors->add(new StreetSplittingError($address->getId()));

            return;
        }

        foreach ($cart->getLineItems()->getElements() as $lineItem) {
            $deliveryInformation = $lineItem->getDeliveryInformation();

            /** @psalm-suppress DeprecatedMethod */
            if (null !== $deliveryInformation && 0.0 === $deliveryInformation->getWeight()) {
                $errors->add(new NullWeightError($cart->getToken()));

                return;
            }
        }

        $phoneNumber = $address->getPhoneNumber();

        if (null === $phoneNumber) {
            $errors->add(new InvalidPhoneNumberError($address->getId()));

            return;
        }

        $phoneNumber = str_replace(['+48', '+', '-', ' '], '', $phoneNumber);

        preg_match(self::PHONE_NUMBER_REGEX, $phoneNumber, $phoneNumberMatches);

        if ([] === $phoneNumberMatches || self::PHONE_NUMBER_LENGTH !== strlen($phoneNumberMatches[0])) {
            $errors->add(new InvalidPhoneNumberError($address->getId()));

            return;
        }

        if ($phoneNumber !== $phoneNumberMatches[0]) {
            $address->setPhoneNumber($phoneNumberMatches[0]);
        }
    }

    private function isPostCodeValid(string $postCode): bool
    {
        return (bool) preg_match(self::POST_CODE_REGEX, $postCode);
    }

    private function checkPostCodeValidity(
        string $postCode,
        string $addressId,
        ErrorCollection $errors
    ): void {
        if (!$this->isPostCodeValid($postCode)) {
            $postCode = trim(substr_replace($postCode, '-', 2, 0));

            if (!$this->isPostCodeValid($postCode)) {
                $errors->add(new InvalidPostCodeError($addressId));
            }
        }
    }

    private function getTechnicalName(SalesChannelContext $context): ?string
    {
        $technicalName = null;
        $shippingMethod = $context->getShippingMethod();
        $shippingMethodCustomFields = $shippingMethod->getCustomFields();

        if (isset($shippingMethodCustomFields['technical_name'])) {
            $technicalName = $shippingMethodCustomFields['technical_name'];
        }

        if (isset($shippingMethod->getTranslated()['customFields']['technical_name'])) {
            $technicalName = $shippingMethod->getTranslated()['customFields']['technical_name'];
        }

        return $technicalName;
    }
}
