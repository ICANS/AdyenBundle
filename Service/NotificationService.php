<?php

namespace Sparkling\AdyenBundle\Service;

class NotificationService
{
    /**
     * @var \Sparkling\AdyenBundle\Service\AdyenService
     */
    protected $adyen;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $logDirectory;

    public function __construct(AdyenService $adyen, $logDirectory)
    {
        $this->adyen = $adyen;
        $this->logDirectory = $logDirectory;
    }

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function sendNotification($request)
    {
        if (is_array($request->notification->notificationItems->NotificationRequestItem)) {
            foreach($request->notification->notificationItems->NotificationRequestItem AS $item)
                $this->process($item);
        } else {
            $this->process($request->notification->notificationItems->NotificationRequestItem);
        }

        $this->em->flush();

        return array("notificationResponse" => "[accepted]");
    }

    protected function process($item)
    {
        $output = print_r($item, true) . PHP_EOL;

        $this->adyen->processNotification(array(
            'merchantReference' => $item->merchantReference,
            'pspReference'      => $item->pspReference,
            'paymentMethod'     => $item->paymentMethod,
            'reason'            => $item->reason,
            'success'           => $item->success,
            'eventCode'         => $item->eventCode
        ));

        file_put_contents($this->logDirectory . '/adyen.log', $output, FILE_APPEND);
    }
}
