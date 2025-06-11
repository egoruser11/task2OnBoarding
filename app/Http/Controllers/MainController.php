<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Http\Services\kommoService;
use Illuminate\Support\Str;

class MainController
{
    public function __construct(private kommoService $kommoService)
    {

    }

    public function indexContacts()
    {

        return $this->kommoService->getContacts();
    }

    public function deleteField()
    {
        $this->kommoService->deleteContactCustomFields([823249,823251,823299,826572]);
    }

    public function indexFields()
    {
        return $this->kommoService->findFeildId("leads","Date of end lead");
    }

    public function contactsCreate()
    {
        return view('contacts.create');
    }

    public function storeContact(StoreRequest $request)
    {
        $data = $request->validated();
        $result = [];
        $result[] =  $this->kommoService->storeContact($data);
        $num = mt_rand(1, 9999);
        $result[] = $this->kommoService->createLeadWithContact('New lead' . " " . "{$num}", $this->kommoService->getLastId("contacts"));
        $number1 = mt_rand(0, 99);
        $number2 = mt_rand(0, 99);
        $products = ['продукт номер' . " {$number1}" , 'продукт номер' . " {$number2}"];
        $result[] = $this->kommoService->attachProductsToLead($this->kommoService->getLastId("leads"), $products);
        return $result;
    }

    public function refresh()
    {
        return $this->kommoService->refreshTokens();
    }


}
