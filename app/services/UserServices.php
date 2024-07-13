<?php
namespace App\services;

use App\Models\VtuappCustomuser;

class UserServices
{
    public function account($id)
    {
        return VtuappCustomuser::where('id', $id)->first();
    }

    public function wallet($id)
    {
        $user = VtuappCustomuser::where('id', $id)->first();
        return $user->Account_Balance;
    }

    public function update($id, array $newData)
    {
        return VtuappCustomuser::whereId($id)->update($newData);
    }

    public function checkPin($id, $userpin)
    {
        $user = VtuappCustomuser::where('id', $id)->first();
        $pin = $user->pin;
        if ($pin == $userpin) {
            return true;
        }

        return false;
    }
}
