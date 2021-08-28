<?php

namespace App\Domain\Splitbill\Bill;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\User\UserService;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Main\Translator;
use App\Domain\Splitbill\BaseBillWriter;

class BillWriter extends BaseBillWriter {

    protected $user_service;
    protected $bill_notification_service;
    protected $finance_service;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            Translator $translation, 
            BillMapper $mapper,
            FinancesService $finance_service,
            BillNotificationService $bill_notification_service,
            UserService $user_service,
            SplitbillBillService $service) {
        parent::__construct($logger, $user, $activity, $group_service, $group_mapper, $translation);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->bill_notification_service = $bill_notification_service;
        $this->finance_service = $finance_service;
        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        $payload = parent::save($id, $data, $additionalData);
        $bill = $payload->getResult();
                
        if($payload->getStatus() == Payload::$NO_ACCESS){
            return $payload;
        }
        
        $group = $this->group_service->getFromHash($additionalData["group"]);
        $users = $this->user_service->getAll();
        $balances = $this->getMapper()->getBalance($bill->id);
        $totalValue = $this->getMapper()->getBillSpend($bill->id);

        foreach ($balances as $balance) {
            /**
             * Create Finance Entry for User
             */
            $userObj = $users[$balance["user"]];

            if ($group->add_finances > 0 && $bill->settleup != 1 && $userObj->module_finance == 1) {
                if ($balance["spend"] > 0) {
                    $entry = new FinancesEntry([
                        "date" => $bill->date,
                        "time" => $bill->time,
                        "description" => $bill->name,
                        "type" => 0,
                        "value" => $balance["spend"],
                        "user" => $balance["user"],
                        "common" => 1,
                        "common_value" => $totalValue,
                        "bill" => $bill->id,
                        "lng" => $bill->lng,
                        "lat" => $bill->lat,
                        "acc" => $bill->acc,
                        "paymethod" => $balance["paymethod"]
                    ]);

                    $entry->category = $this->finance_service->getDefaultOrAssignedCategory($balance["user"], $entry);
                    $this->finance_service->addOrUpdateFromBill($entry);
                } else {
                    $this->finance_service->deleteEntrywithBill($bill->id, $balance["user"]);
                }
            }
        }

        /**
         * Notify Users
         */
        $is_new_bill = $payload->getStatus() == Payload::$STATUS_NEW;
        $this->bill_notification_service->notifyUsers("edit", $bill, $group, $is_new_bill);

        return $payload;
    }

    public function getParentMapper() {
        return $this->group_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'splitbill_bills';
    }

    public function getObjectViewRouteParams($entry): array {
        $group = $this->getParentMapper()->get($entry->getParentID());
        return [
            "group" => $group->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "splitbills";
    }

}
