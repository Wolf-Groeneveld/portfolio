<?php

declare(strict_types=1);

/**
 * Aansluitpunt voor een latere betaalprovider (Mollie of Stripe).
 * Er staan hier geen API-sleutels en er wordt geen transactie aangemaakt.
 * Zolang payment_can_charge() false is, mag de site geen betaling als geslaagd tonen.
 */
function payment_provider_name(): string
{
    return 'none';
}

function payment_can_charge(): bool
{
    return false;
}
