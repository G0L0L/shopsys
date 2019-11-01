# Upgrade Instructions for rounding by Currency

There is a new way of rounding prices. Previously global rounding setting was removed.
Rounding depends on currency settings, which can be managed by administrator. In addition, administrator 
can manage minimum fraction digits, which will be displayed on fronted, for every currency separately.

To avoid of [BC breaks](/docs/contributing/backward-compatibility-promise.md), new functions for rounding by currency have been implemented.
Because of new functions, new tests have been introduced.

### New tests for price formatting 
- add tests for `NumberFormatHelper`
```
    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createForCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVatWithCurrency($price, $firstDomainDefaultCurrency)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }
```
```
    /**
     * Inspired by formatCurrency() method, {@see \Shopsys\FrameworkBundle\Twig\PriceExtension}
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function getFormattedPriceRoundedByCurrencyOnFrontend(Money $price): string
    {
        $firstDomainDefaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $firstDomainLocale = $this->localizationHelper->getFrontendLocale();
        $currencyFormatter = $this->currencyFormatterFactory->createForCurrency($firstDomainLocale, $firstDomainDefaultCurrency);

        $intlCurrency = $this->intlCurrencyRepository->get($firstDomainDefaultCurrency->getCode(), $firstDomainLocale);

        $formattedPriceWithCurrencySymbol = $currencyFormatter->format(
            $this->rounding->roundPriceWithVatWithCurrency($price, $firstDomainDefaultCurrency)->getAmount(),
            $intlCurrency->getCurrencyCode()
        );

        return $this->normalizeSpaces($formattedPriceWithCurrencySymbol);
    }
```
- add test for `CartBoxPage`
```
    /**
     * @param int $expectedCount
     * @param string $expectedPrice
     */
    public function seeCountAndPriceRoundedByCurrencyInCartBox(int $expectedCount, string $expectedPrice): void
    {
        $convertedPrice = Money::create($this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice));
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend($convertedPrice);
        $messageId = '{1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>';
        $translatedMessageWithTags = tc($messageId, $expectedCount, ['%itemsCount%' => $expectedCount, '%priceWithVat%' => $expectedFormattedPriceWithCurrency], 'messages', $this->tester->getFrontendLocale());

        $this->tester->seeInCss(strip_tags($translatedMessageWithTags), '.js-cart-info');
    }
```
- add tests for `CartPage`
```
    /**
     * @param string $productName
     * @param string $price
     */
    public function assertProductPriceRoundedCurrency($productName, $price)
    {
        $convertedPrice = $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($price);
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money::create($convertedPrice));
        $productPriceCell = $this->getProductTotalPriceCellByName($productName);
        $this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
    }
```
```
    /**
     * @param string $price
     */
    public function assertTotalPriceWithVatRoundedByCurrency($price)
    {
        $formattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(Money::create($price));
        $orderPriceCell = $this->getTotalProductsPriceCell();
        $message = t('Total price including VAT', [], 'messages', $this->tester->getFrontendLocale());
        $this->tester->seeInElement($message . ': ' . $formattedPriceWithCurrency, $orderPriceCell);
    }
```
### New tests for rounding by currency
- add tests for `RoundingTest`
```
    /**
     * @dataProvider roundingProvider
     * @param mixed $unroundedPrice
     * @param mixed $expectedAsPriceWithVat
     * @param mixed $expectedAsPriceWithoutVat
     * @param mixed $expectedAsVatAmount
     */
   public function testRoundingWithCurrency(
        $unroundedPrice,
        $expectedAsPriceWithVat,
        $expectedAsPriceWithoutVat,
        $expectedAsVatAmount
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rounding = new Rounding($pricingSettingMock);

        $currencyData = new CurrencyData();
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $this->assertThat($rounding->roundPriceWithVatWithCurrency($unroundedPrice, $currency), new IsMoneyEqual($expectedAsPriceWithVat));
        $this->assertThat($rounding->roundPriceWithoutVat($unroundedPrice), new IsMoneyEqual($expectedAsPriceWithoutVat));
        $this->assertThat($rounding->roundVatAmount($unroundedPrice), new IsMoneyEqual($expectedAsVatAmount));
    }
``` 
```
    /**
     * @dataProvider roundingPriceWithVatProvider
     * @param mixed $roundingType
     * @param mixed $inputPrice
     * @param mixed $outputPrice
     */
    public function testRoundingPriceWithVatWithCurrency(
        $roundingType,
        $inputPrice,
        $outputPrice
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $currencyRoundingType = $this->converPricingRoundingTypeToCurrencyRoundingType($roundingType);

        $currencyData = new CurrencyData();
        $currencyData->roundingType = $currencyRoundingType;
        $currency = new Currency($currencyData);

        $rounding = new Rounding($pricingSettingMock);
        $roundedPrice = $rounding->roundPriceWithVatWithCurrency($inputPrice, $currency);

        $this->assertThat($roundedPrice, new IsMoneyEqual($outputPrice));
    }

    /**
     * @param int $roundingType
     * @return string
     */
    private function converPricingRoundingTypeToCurrencyRoundingType(int $roundingType)
    {
        switch ($roundingType) {
            case 1:
                return Currency::ROUNDING_TYPE_HUNDREDTHS;

            case 2:
                return Currency::ROUNDING_TYPE_FIFTIES;
                break;

            case 3:
                return Currency::ROUNDING_TYPE_INTEGER;
                break;

            default:
                throw new InvalidCurrencyRoundingTypeException(
                    sprintf('Rounding type %s is not valid', $roundingType)
                );
        }
    }
```

### Change existing tests to use rounding by currency
- change test `OrderPreviewCalculationTest::testCalculatePreviewWithTransportAndPayment()`
```diff
    $quantifiedProductDiscountCalculationMock = $this->getMockBuilder(QuantifiedProductDiscountCalculation::class)
    - ->setMethods(['calculateDiscounts', '__construct'])
    + ->setMethods(['calculateDiscountsWithCurrency', '__construct'])
      ->disableOriginalConstructor()
      ->getMock();
    - $quantifiedProductDiscountCalculationMock->expects($this->once())->method('calculateDiscounts')
    + $quantifiedProductDiscountCalculationMock->expects($this->once())->method('calculateDiscountsWithCurrency')
       ->willReturn($quantifiedProductsDiscounts);
```
- change test `OrderPreviewCalculationTest::testCalculatePreviewWithoutTransportAndPayment()`
```diff
    $quantifiedProductDiscountCalculationMock = $this->getMockBuilder(QuantifiedProductDiscountCalculation::class)
    - ->setMethods(['calculateDiscounts', '__construct'])
    + ->setMethods(['calculateDiscountsWithCurrency', '__construct'])
      ->disableOriginalConstructor()
      ->getMock();
    - $quantifiedProductDiscountCalculationMock->expects($this->once())->method('calculateDiscounts')
    + $quantifiedProductDiscountCalculationMock->expects($this->once())->method('calculateDiscountsWithCurrency')
       ->willReturn($quantifiedProductsDiscounts);
```

### Deprecated functions and test
- these methods are deprecated and will be removed in the next major release
    - `CurrencyFormatterFactory::create()` use `createForCurrency()` instead
- these tests are deprecated and will be removed in the next major release
    - `CartBoxPage::seeCountAndPriceInCartBox()`
    - `CartPage::assertProductPriceRoundedCurrency()`
    - `CartPage::assertTotalPriceWithVatRoundedByCurrency()`
    - `NumberFormatHelper::getFormattedPriceWithCurrencySymbolOnFrontend()`
    - `NumberFormatHelper::getFormattedPriceOnFrontend()`
