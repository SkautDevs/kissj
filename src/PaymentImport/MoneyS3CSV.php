<?php

namespace kissj\PaymentImport;

class MoneyS3CSV implements ManualPaymentImporter {

    protected $file;
    protected $event;

    public function __construct(string $file, string $event = "") {
        $this->file = $file;
        $this->event = $event;
    }

    public function getName(): string {
        return "Money S3 CSV";
    }

    protected function encode($str) {
        return iconv("Windows-1250", "UTF-8", $str);
    }

    /**
     * @return array of kissj\PaymentImport\Payment, array of string
     */
    public function getPayments(): array {
        $payments = array();
        $errors = array();
        if (($handle = fopen($this->file, "r")) !== FALSE) {
            $header_found = FALSE;
            while (($header = fgetcsv($handle, 0, ";")) !== FALSE) {
                if (count($header) > 0 && $header[0] == "Detail 1") {
                    $header_found = TRUE;
                    break;
                }
            }
            if (!$header_found || $header === FALSE || count($header) < 35 || $header[0] != "Detail 1" || $header[1] != "0")
                throw new \RuntimeException("File ".$this->file." is not a properly formatted Money S3 CSV.");

            $header = array_map(array($this, 'encode'), $header);

            $fields = array_flip($header);
            $header_length = count($header);

            $vsField = $fields["Variabilní symbol"];
            $senderNameField = $fields["Název firmy odběratele"];
            $amountField = $fields["Celková částka - valuty"];
            $currencyField = $fields["Měna"];
            $noteForReceiverField = $fields["Popis dokladu"];
            $dateReceivedField = $fields["Datum platby"];

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                if (count($data) < $header_length || $data[0] != "Detail 1" || $data[1] != "1")
                    continue;

                try {
                    $data = array_map(array($this, 'encode'), $data);

                    $payment = new Payment();
                    $payment->event = $this->event;
                    $payment->variableSymbol = (int)$data[$vsField];
                    $payment->senderName = $data[$senderNameField];
                    $payment->senderAccountNr = "";
                    $payment->amount = (float)$data[$amountField];
                    $payment->currency = $data[$currencyField];
                    $payment->noteForReceiver = $data[$noteForReceiverField];
                    $payment->dateReceived = new \DateTimeImmutable($data[$dateReceivedField]);
                    $payments[] = $payment;
                } catch (\Exception $e) {
                    $errors[] = $data;
                }
            }
            fclose($handle);
        }

        return array($payments, $errors);
    }
}
