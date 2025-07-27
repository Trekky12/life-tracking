<?php


namespace App\Domain\Splitbill;

use App\Domain\Service;

abstract class BaseBillService extends Service {

    protected function calculateBalance($group) {
        $balance = $this->mapper->getTotalBalance($group);
        $settled = $this->mapper->getSettledUpSpendings($group, 1);

        $me = intval($this->current_user->getUser()->id);

        if (!array_key_exists($me, $balance)) {
            return array($balance, null);
        }

        $my_balance = $balance[$me]["balance"];

        foreach ($balance as $user_id => &$b) {

            $b["settled"] = array_key_exists($user_id, $settled) ? $settled[$user_id] : 0;

            if ($user_id !== $me) {

                // i owe money
                if ($my_balance < 0 && $b["balance"] > 0) {

                    // another person owes the user money
                    // but my debit is now settled
                    if ($b["balance"] > abs($my_balance)) {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                    // I'm the only one who owes this user money
                    // my debit is now lower
                    else {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                }


                // someone owes me money
                if ($my_balance > 0 && $b["balance"] < 0) {

                    // another user owes me money
                    if ($my_balance > abs($b["balance"])) {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                    // only this user owes me money
                    // my credit is settled
                    else {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                }
            }
        }

        $filtered = array_filter($balance, function ($b) use ($me) {
            return $b["user"] != $me && ($b['balance'] != 0 or $b['owe'] != 0);
        });
        /**
         * Resort Balances
         * Big credits on top, big debits on top 
         */
        uasort($filtered, function ($a, $b) {
            if ($b['owe'] > 0) {
                return $b['owe'] - $a['owe'];
            }
            return $a['owe'] - $b['owe'];
        });

        $my_balance_overview = array_key_exists($me, $balance) ? $balance[$me] : null;
        return array($filtered, $my_balance_overview);
    }
}
