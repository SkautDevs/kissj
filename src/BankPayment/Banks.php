<?php
/**
 * Created by PhpStorm.
 * User: Martin Pecka
 * Date: 8.5.2018
 * Time: 22:41
 */

namespace kissj\BankPayment;


class Banks {
    protected $banks = [
        'fio' => [
            'bankCode' => '2010',
            'name' => 'Fio Banka',
            'importer' => \kissj\PaymentImport\FioPaymentImporter::class
        ],
        'air' => [
            'bankCode' => '3030',
            'name' => 'Air Bank',
        ],
        'ano' => [
            'bankCode' => '2260',
            'name' => 'ANO spořitelní družstvo',
        ],
        'artesa' => [
            'bankCode' => '2220',
            'name' => 'Artesa spořitelní družstvo',
        ],
        'gutmann' => [
            'bankCode' => '8231',
            'name' => 'Bank Gutmann Aktiengesellschaft',
        ],
        'china' => [
            'bankCode' => '8250',
            'name' => 'Bank of China',
        ],
        'creditas' => [
            'bankCode' => '2250',
            'name' => 'Banka CREDITAS a.s.',
        ],
        'bnp' => [
            'bankCode' => '6300',
            'name' => 'BNP Paribas Fortis SA/NV',
        ],
        'bnp-personal' => [
            'bankCode' => '3050',
            'name' => 'BNP Paribas Personal Finance SA',
        ],
        'ceb' => [
            'bankCode' => '8090',
            'name' => 'Česká exportní banka, a.s.',
        ],
        'cnb' => [
            'bankCode' => '0710',
            'name' => 'Česká národní banka',
        ],
        'cs' => [
            'bankCode' => '0800',
            'name' => 'Česká spořitelna, a.s.',
        ],
        'cmss' => [
            'bankCode' => '7960',
            'name' => 'Českomoravská stavební spořitelna a.s.',
        ],
        'czrb' => [
            'bankCode' => '4300',
            'name' => 'Českomoravská záruční a rozvojová banka, a.s.',
        ],
        'csob' => [
            'bankCode' => '0300',
            'name' => 'Československá obchodní banka, a.s.',
        ],
        'cud' => [
            'bankCode' => '2030',
            'name' => 'Československé úvěrní družstvo',
        ],
        'citfin' => [
            'bankCode' => '2060',
            'name' => 'Citfin spořitelní družstvo',
        ],
        'citibank' => [
            'bankCode' => '2600',
            'name' => 'Citibank Europe plc',
        ],
        'commerz' => [
            'bankCode' => '6200',
            'name' => 'COMMERZBANK Aktiengesellschaft',
        ],
        'db' => [
            'bankCode' => '7910',
            'name' => 'Deutsche Bank Aktiengesellschaft',
        ],
        'kredit' => [
            'bankCode' => '8240',
            'name' => 'Družstevní záložna Kredit',
        ],
        'equa' => [
            'bankCode' => '6100',
            'name' => 'Equa bank a.s.',
        ],
        'expo' => [
            'bankCode' => '4000',
            'name' => 'Expobank CZ a.s.',
        ],
        'hsbc' => [
            'bankCode' => '8150',
            'name' => 'HSBC Bank plc',
        ],
        'hb' => [
            'bankCode' => '2100',
            'name' => 'Hypoteční banka, a.s.',
        ],
        'industrial' => [
            'bankCode' => '8265',
            'name' => 'Industrial and Commercial Bank of China Limited',
        ],
        'ing' => [
            'bankCode' => '3500',
            'name' => 'ING Bank N.V.',
        ],
        'jnt' => [
            'bankCode' => '5800',
            'name' => 'J & T BANKA, a.s.',
        ],
        'kb' => [
            'bankCode' => '0100',
            'name' => 'Komerční banka, a.s.',
        ],
        'mbank' => [
            'bankCode' => '6210',
            'name' => 'mBank S.A.',
        ],
        'pyramida' => [
            'bankCode' => '7990',
            'name' => 'Modrá pyramida stavební spořitelna, a.s.',
        ],
        'moneta' => [
            'bankCode' => '0600',
            'name' => 'MONETA Money Bank, a.s.',
        ],
        'mpu' => [
            'bankCode' => '2070',
            'name' => 'Moravský Peněžní Ústav - spořitelní družstvo',
        ],
        'mufg' => [
            'bankCode' => '2020',
            'name' => 'MUFG Bank (Europe) N.V.',
        ],
        'ober' => [
            'bankCode' => '8040',
            'name' => 'Oberbank AG',
        ],
        'pd' => [
            'bankCode' => '2200',
            'name' => 'Peněžní dům, spořitelní družstvo',
        ],
        'pko' => [
            'bankCode' => '3060',
            'name' => 'PKO BP S.A.,',
        ],
        'pb' => [
            'bankCode' => '2240',
            'name' => 'Poštová banka, a.s.',
        ],
        'ppf' => [
            'bankCode' => '6000',
            'name' => 'PPF banka a.s.',
        ],
        'privat' => [
            'bankCode' => '8200',
            'name' => 'PRIVAT BANK der Raiffeisenlandesbank',
        ],
        'raiffss' => [
            'bankCode' => '7950',
            'name' => 'Raiffeisen stavební spořitelna a.s.',
        ],
        'raiff' => [
            'bankCode' => '5500',
            'name' => 'Raiffeisenbank a.s.',
        ],
        'saxo' => [
            'bankCode' => '8211',
            'name' => 'Saxo Bank A/S',
        ],
        'sber' => [
            'bankCode' => '6800',
            'name' => 'Sberbank CZ, a.s.',
        ],
        'sscs' => [
            'bankCode' => '8060',
            'name' => 'Stavební spořitelna České spořitelny, a.s.',
        ],
        'sumo' => [
            'bankCode' => '8241',
            'name' => 'Sumitomo Mitsui Banking Corporation Europe Limited',
        ],
        'unicredit' => [
            'bankCode' => '2700',
            'name' => 'UniCredit Bank',
        ],
        'volksbank' => [
            'bankCode' => '8030',
            'name' => 'Volksbank Raiffeisenbank Nordoberpfalz eG pobočka Cheb',
        ],
        'vub' => [
            'bankCode' => '6700',
            'name' => 'VUB, a.s.',
        ],
        'sparkasse' => [
            'bankCode' => '7940',
            'name' => 'Waldviertler Sparkasse Bank AG',
        ],
        'western' => [
            'bankCode' => '3040',
            'name' => 'Western Union International Bank GmbH',
        ],
        'wuestss' => [
            'bankCode' => '7970',
            'name' => 'Wüstenrot - stavební spořitelna a.s.',
        ],
        'wuest' => [
            'bankCode' => '7980',
            'name' => 'Wüstenrot hypoteční banka a.s.',
        ]
    ];

    public function getBanks() {
        return $this->banks;
    }
}
