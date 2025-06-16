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
        $this->kommoService->deleteContactCustomFields([843138,823249,823299,823251]);
    }

    public function indexFields()
    {
        return $this->kommoService->getCustomFieldsNames('contacts');
    }

    public function contactsCreate()
    {
        return view('contacts.create');
    }

    public function storeContact(StoreRequest $request)
    {
        $data = $request->validated();
        $result = [];
        $result['contact_id'] =  $this->kommoService->storeContact($data);
        $num = mt_rand(1, 9999);
        $num1 = $num + 1;
        $leads_ids = [];
        $leads_ids[] = $this->kommoService->createLeadWithContact('New lead' . " " . "{$num}", $this->kommoService->getLastId("contacts"));
        $leads_ids[] = $this->kommoService->createLeadWithContact('New lead' . " " . "{$num1}", $this->kommoService->getLastId("contacts"));
        $result['leads_ids'] = $leads_ids;
        $number1 = mt_rand(0, 99);
        $number2 = mt_rand(0, 99);
        $products = ['продукт номер' . " {$number1}" , 'продукт номер' . " {$number2}"];
        $attach = $this->kommoService->attachProductsToLead($this->kommoService->getLastId("leads"), $products);
        $result['products_ids'] = $attach['product_ids'];
        $result['catalog_id'] = $attach['catalog_id'];
        return view('result',compact('result'));
    }

    public function refresh()
    {
        return $this->kommoService->refreshTokens();
    }


}
