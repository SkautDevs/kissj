<?php declare(strict_types=1);

namespace kissj\Event;

use kissj\Event\ContentArbiter\ContentArbiterItem;
use kissj\Event\ContentArbiter\ContentArbiterItemType;

abstract class AbstractContentArbiter
{
    public ContentArbiterItem $contingent;
    public ContentArbiterItem $patrolName;
    public ContentArbiterItem $firstName;
    public ContentArbiterItem $lastName;
    public ContentArbiterItem $nickname;
    public ContentArbiterItem $address;
    public ContentArbiterItem $phone;
    public ContentArbiterItem $gender;
    public ContentArbiterItem $country;
    public ContentArbiterItem $email;
    public ContentArbiterItem $unit;
    public ContentArbiterItem $languages;
    public ContentArbiterItem $birthDate;
    public ContentArbiterItem $birthPlace;
    public ContentArbiterItem $health;
    public ContentArbiterItem $medicaments;
    public ContentArbiterItem $psychicalHealth;
    public ContentArbiterItem $emergencyContact;
    public ContentArbiterItem $food;
    public ContentArbiterItem $idNumber;
    public ContentArbiterItem $scarf;
    public ContentArbiterItem $swimming;
    public ContentArbiterItem $tshirt;
    public ContentArbiterItem $arrivalDate;
    public ContentArbiterItem $departureDate;
    public ContentArbiterItem $uploadFile;
    public ContentArbiterItem $skills;
    public ContentArbiterItem $preferredPosition;
    public ContentArbiterItem $driver;
    public ContentArbiterItem $printedHandbook;
    public ContentArbiterItem $notes;

    public function __construct()
    {
        $this->contingent = new ContentArbiterItem(
            id: 'contingent',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 10,
            label: 'detail.contingentTitle',
            placeholder: '',
        );
        $this->patrolName = new ContentArbiterItem(
            id: 'patrolName',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 20,
            label: 'detail.patrolName',
            placeholder: 'detail.patrolNamePlaceholder',
        );
        $this->firstName = new ContentArbiterItem(
            id: 'firstName',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 30,
            label: 'detail.name',
            placeholder: 'detail.namePlaceholder',
        );
        $this->lastName = new ContentArbiterItem(
            id: 'lastName',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 40,
            label: 'detail.surname',
            placeholder: 'detail.surnamePlaceholder',
        );
        $this->nickname = new ContentArbiterItem(
            id: 'nickname',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 50,
            label: 'detail.nickname',
            placeholder: 'detail.nicknamePlaceholder',
            required: false,
        );
        $this->gender = new ContentArbiterItem(
            id: 'gender',
            allowed: true,
            type: ContentArbiterItemType::Select,
            order: 60,
            label: 'detail.gender',
            placeholder: '',
            options: ['detail.male', 'detail.female', 'detail.other'],
        );
        $this->birthDate = new ContentArbiterItem(
            id: 'birthDate',
            allowed: true,
            type: ContentArbiterItemType::Date,
            order: 70,
            label: 'detail.birthDate',
            placeholder: '',
        );
        $this->birthPlace = new ContentArbiterItem(
            id: 'birthPlace',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 80,
            label: 'detail.birthPlace',
            placeholder: 'detail.birthPlacePlaceholder',
        );
        $this->uploadFile = new ContentArbiterItem(
            id: 'uploadFile',
            allowed: false,
            type: ContentArbiterItemType::File,
            order: 90,
            label: 'detail.uploadFile',
            placeholder: '',
            required: false,
        );
        $this->idNumber = new ContentArbiterItem(
            id: 'idNumber',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 100,
            label: 'detail.idNumber',
            placeholder: 'detail.idNumberPlaceholder',
        );
        $this->address = new ContentArbiterItem(
            id: 'permanentResidence',
            allowed: true,
            type: ContentArbiterItemType::Textarea,
            order: 110,
            label: 'detail.address',
            placeholder: 'detail.addressPlaceholder',
        );
        $this->country = new ContentArbiterItem(
            id: 'country',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 120,
            label: 'detail.country',
            placeholder: '',
        );
        $this->email = new ContentArbiterItem(
            id: 'email',
            allowed: false,
            type: ContentArbiterItemType::Email,
            order: 130,
            label: 'detail.email',
            placeholder: 'detail.emailPlaceholder',
        );
        $this->phone = new ContentArbiterItem(
            id: 'telephoneNumber',
            allowed: false,
            type: ContentArbiterItemType::Phone,
            order: 140,
            label: 'detail.phone',
            placeholder: 'detail.phonePlaceholder',
            pattern: '^\+?[0-9 ]+$',
        );
        $this->unit = new ContentArbiterItem(
            id: 'scoutUnit',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 150,
            label: 'detail.unit',
            placeholder: 'detail.unitPlaceholder',
        );
        $this->languages = new ContentArbiterItem(
            id: 'languages',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 160,
            label: 'detail.languages',
            placeholder: 'detail.languagesPlaceholder',
        );
        $this->tshirt = new ContentArbiterItem(
            id: 'tshirt',
            allowed: false,
            type: ContentArbiterItemType::TshirtComposite,
            order: 170,
            label: 'detail.tshirt',
            placeholder: '',
        );
        $this->food = new ContentArbiterItem(
            id: 'foodPreferences',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 180,
            label: 'detail.food',
            placeholder: '',
        );
        $this->health = new ContentArbiterItem(
            id: 'healthProblems',
            allowed: true,
            type: ContentArbiterItemType::Textarea,
            order: 190,
            label: 'detail.health',
            placeholder: 'detail.healthPlaceholder',
            required: false,
        );
        $this->medicaments = new ContentArbiterItem(
            id: 'medicaments',
            allowed: false,
            type: ContentArbiterItemType::Textarea,
            order: 200,
            label: 'detail.medicaments',
            placeholder: 'detail.medicamentsPlaceholder',
            required: false,
        );
        $this->psychicalHealth = new ContentArbiterItem(
            id: 'psychicalHealthProblems',
            allowed: true,
            type: ContentArbiterItemType::Textarea,
            order: 210,
            label: 'detail.psychicalHealth',
            placeholder: 'detail.psychicalHealthPlaceholder',
            required: false,
        );
        $this->emergencyContact = new ContentArbiterItem(
            id: 'emergencyContact',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 220,
            label: 'detail.emergencyContact',
            placeholder: 'detail.emergencyContactPlaceholder',
        );
        $this->swimming = new ContentArbiterItem(
            id: 'swimming',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 230,
            label: 'detail.swimming',
            placeholder: '',
            options: ['detail.swimmingGood', 'detail.swimmingAverage', 'detail.swimmingBad'],
        );
        $this->scarf = new ContentArbiterItem(
            id: 'scarf',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 240,
            label: 'detail.scarf',
            placeholder: 'detail.scarfPlaceholder',
            required: false,
        );
        $this->arrivalDate = new ContentArbiterItem(
            id: 'arrivalDate',
            allowed: false,
            type: ContentArbiterItemType::Date,
            order: 250,
            label: 'detail.arrivalDate',
            placeholder: '',
        );
        $this->departureDate = new ContentArbiterItem(
            id: 'departureDate',
            allowed: false,
            type: ContentArbiterItemType::Date,
            order: 260,
            label: 'detail.departureDate',
            placeholder: '',
        );
        $this->driver = new ContentArbiterItem(
            id: 'driversLicense',
            allowed: false,
            type: ContentArbiterItemType::Checkbox,
            order: 270,
            label: 'detail.driver',
            placeholder: '',
            required: false,
        );
        $this->skills = new ContentArbiterItem(
            id: 'skills',
            allowed: false,
            type: ContentArbiterItemType::Textarea,
            order: 280,
            label: 'detail.skills',
            placeholder: 'detail.skillsPlaceholder',
            required: false,
        );
        $this->preferredPosition = new ContentArbiterItem(
            id: 'preferredPosition',
            allowed: false,
            type: ContentArbiterItemType::Checkbox,
            order: 290,
            label: 'detail.preferredPosition',
            placeholder: '',
            required: false,
        );
        $this->printedHandbook = new ContentArbiterItem(
            id: 'printedHandbook',
            allowed: false,
            type: ContentArbiterItemType::Checkbox,
            order: 300,
            label: 'detail.printedHandbook',
            placeholder: '',
            required: false,
        );
        $this->notes = new ContentArbiterItem(
            id: 'notes',
            allowed: true,
            type: ContentArbiterItemType::Textarea,
            order: 310,
            label: 'detail.notes',
            placeholder: 'detail.notesPlaceholder',
            required: false,
        );
    }

    /**
     * @return list<ContentArbiterItem>
     */
    public function getAllItems(): array
    {
        return [
            $this->contingent,
            $this->patrolName,
            $this->firstName,
            $this->lastName,
            $this->nickname,
            $this->gender,
            $this->birthDate,
            $this->birthPlace,
            $this->uploadFile,
            $this->idNumber,
            $this->address,
            $this->country,
            $this->email,
            $this->phone,
            $this->unit,
            $this->languages,
            $this->tshirt,
            $this->food,
            $this->health,
            $this->medicaments,
            $this->psychicalHealth,
            $this->emergencyContact,
            $this->swimming,
            $this->scarf,
            $this->arrivalDate,
            $this->departureDate,
            $this->driver,
            $this->skills,
            $this->preferredPosition,
            $this->printedHandbook,
            $this->notes,
        ];
    }

    /**
     * @return list<ContentArbiterItem>
     */
    public function getAllowedItems(): array
    {
        $items = array_filter(
            $this->getAllItems(),
            fn (ContentArbiterItem $item) => $item->allowed,
        );

        usort($items, fn (ContentArbiterItem $a, ContentArbiterItem $b) => $a->order <=> $b->order);

        return array_values($items);
    }
}
