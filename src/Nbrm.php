<?php

namespace Nbrm;

use DateTime;
use DateTimeZone;
use SoapClient;

class Nbrm
{
    /**
     * The start date parameter.
     *
     * @var \DateTime|null
     */
    protected $startDate;

    /**
     * The end date parameter.
     *
     * @var \DateTime|null
     */
    protected $endDate;

    /**
     * The exchange rates pulled from the NBRM web service.
     *
     * @var array|null
     */
    protected $rates;

    /**
     * The raw response from the NBRM web service.
     *
     * @var object|null
     */
    protected $response;

    /**
     * Create a new Nbrm instance.
     * 
     * @param mixed $startDate
     * @param mixed $endDate
     * 
     * @return void
     */
    public function __construct(
        $startDate = null,
        $endDate = null
    ) {
        $this->startDate = $this->formatDate($startDate);
        $this->endDate   = $this->formatDate($endDate);

        $this->initialize();
    }

    /**
     * Retrieves data from the NBRM web service and stores the response.
     *
     * @throws \SoapFault  Thrown if there's a problem in the communication between the library and the web service.
     * @throws \UnexpectedValueException  Thrown if the web service response cannot be readily parsed.
     * 
     * @return void
     */
    public function initialize()
    {
        $rates = null;
        $client = new SoapClient("http://www.nbrm.mk/klservice/kurs.asmx?wsdl");

        $params = [
            'StartDate' => $this->startDate->format('d.m.Y'),
            'EndDate'   => $this->endDate->format('d.m.Y')
        ];

        $this->response = $client->GetExchangeRate($params);
        $xml = simplexml_load_string($this->response->GetExchangeRateResult);

        if (! $xml->KursZbir) {
            throw new \UnexpectedValueException("Can't parse web service response.");
        }

        foreach ($xml->KursZbir as $currency) {
            if ($date = $this->formatDate($currency->Datum)) {
                if ($rates === null || ! array_key_exists((string) $currency->Oznaka, $rates)) {
                    $rates[(string) $currency->Oznaka] = [
                        'number'    => (int) $currency->Valuta,
                        'name'      => (string) $currency->NazivAng,
                        'country'   => (string) $currency->DrzavaAng,
                        'mkName'    => (string) $currency->NazivMak,
                        'mkCountry' => (string) $currency->Drzava,
                    ];
                }

                $rates[(string) $currency->Oznaka]['rates'][$date->format('Y-m-d')] = (double) $currency->Sreden;
            }
        }

        $this->rates = $rates;
    }

    /**
     * Returns the formatted rates.
     * 
     * @return array|null
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Returns the raw response.
     * 
     * @return array|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Convert the provided input into a DateTime object.
     * 
     * @param  mixed $date
     * 
     * @return \DateTime
     */
    protected function formatDate($date = null)
    {
        try {
            if ($this->isTimestamp($date)) {
                return (new DateTime(null, new DateTimeZone('UTC')))->setTimestamp($date);
            }

            return new DateTime($date, new DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return new DateTime(null, new DateTimeZone('UTC'));
        }
    }

    /**
     * Checks if a string is a valid timestamp.
     *
     * @param  string $timestamp Timestamp to validate.
     * 
     * @return bool
     */
    protected function isTimestamp($timestamp)
    {
        $check = (is_int($timestamp) OR is_float($timestamp))
            ? $timestamp
            : (string) (int) $timestamp;

        return  ($check === $timestamp)
                AND ((int) $timestamp <=  PHP_INT_MAX)
                AND ((int) $timestamp >= ~PHP_INT_MAX);
    }
}
