<?php

namespace SS6\ShopBundle\Model\Transport;

use SS6\ShopBundle\Model\Pricing\PriceCalculation as GenericPriceCalculation;
use SS6\ShopBundle\Model\Pricing\PricingSetting;

class PriceCalculation {

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\PriceCalculation
	 */
	private $genericPriceCalculation;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\PricingSetting
	 */
	private $pricingSetting;

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\PriceCalculation $genericPriceCalculation
	 * @param \SS6\ShopBundle\Model\Pricing\PricingSetting $pricingSetting
	 */
	public function __construct(
		GenericPriceCalculation $genericPriceCalculation,
		PricingSetting $pricingSetting
	) {
		$this->pricingSetting = $pricingSetting;
		$this->genericPriceCalculation = $genericPriceCalculation;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Transport\Transport $transport
	 * @return \SS6\ShopBundle\Model\Pricing\Price
	 */
	public function calculatePrice(Transport $transport) {
		return $this->genericPriceCalculation->calculatePrice(
			$transport->getPrice(),
			$this->pricingSetting->getInputPriceType(),
			$transport->getVat()
		);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Transport\Transport[] $transports
	 * @return \SS6\ShopBundle\Model\Pricing\Price[] array indices are preserved
	 */
	public function calculatePrices(array $transports) {
		$transportsPrices = array();
		foreach ($transports as $key => $transport) {
			$transportsPrices[$key] = $this->calculatePrice($transport);
		}

		return $transportsPrices;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Transport\Transport[] $transports
	 * @return \SS6\ShopBundle\Model\Pricing\Price[]
	 */
	public function calculatePricesById(array $transports) {
		$transportsPrices = array();
		foreach ($transports as $transport) {
			$transportsPrices[$transport->getId()] = $this->calculatePrice($transport);
		}

		return $transportsPrices;
	}

}
