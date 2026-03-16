<?php

declare(strict_types=1);

namespace kissj\Event;

use kissj\Application\DateTimeUtils;
use kissj\Event\ContentArbiter\AgeGroup;
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
    public ContentArbiterItem $parentalConsent;
    public ContentArbiterItem $hospitalConsent;
    public ContentArbiterItem $childWorkCert;
    public ContentArbiterItem $adultEventCert;
    public ContentArbiterItem $skills;
    public ContentArbiterItem $preferredPosition;
    public ContentArbiterItem $driver;
    public ContentArbiterItem $printedHandbook;
    public ContentArbiterItem $notes;

    public function __construct()
    {
        $this->contingent = new ContentArbiterItem(
            slug: 'contingent',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 10,
            label: 'detail.contingentTitle',
            placeholder: 'detail.contingentPlaceholder',
        );
        $this->patrolName = new ContentArbiterItem(
            slug: 'patrolName',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 20,
            label: 'detail.patrolName',
            placeholder: 'detail.patrolNamePlaceholder',
        );
        $this->firstName = new ContentArbiterItem(
            slug: 'firstName',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 30,
            label: 'detail.firstName',
            placeholder: 'detail.firstNamePlaceholder',
        );
        $this->lastName = new ContentArbiterItem(
            slug: 'lastName',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 40,
            label: 'detail.surname',
            placeholder: 'detail.surnamePlaceholder',
        );
        $this->nickname = new ContentArbiterItem(
            slug: 'nickname',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 50,
            label: 'detail.scoutNick',
            placeholder: 'detail.scoutNickPlaceholder',
            required: false,
        );
        $this->gender = new ContentArbiterItem(
            slug: 'gender',
            allowed: true,
            type: ContentArbiterItemType::Select,
            order: 60,
            label: 'detail.gender',
            placeholder: 'detail.genderPlaceholder',
            options: ['man' => 'detail.genderMan', 'woman' => 'detail.genderWoman', 'other' => 'detail.genderOther'],
        );
        $this->birthDate = new ContentArbiterItem(
            slug: 'birthDate',
            allowed: true,
            type: ContentArbiterItemType::Date,
            order: 70,
            label: 'detail.birthDate',
            placeholder: 'detail.birthDatePlaceholder',
            defaultValue: DateTimeUtils::getDateTime('-18 years')->format('Y-m-d'),
            extraClasses: ['form-wide'],
        );
        $this->birthPlace = new ContentArbiterItem(
            slug: 'birthPlace',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 80,
            label: 'detail.birthPlace',
            placeholder: 'detail.birthPlacePlaceholder',
        );
        $this->idNumber = new ContentArbiterItem(
            slug: 'idNumber',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 100,
            label: 'detail.idNumber',
            placeholder: 'detail.idNumber-placeholder',
        );
        $this->address = new ContentArbiterItem(
            slug: 'permanentResidence',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 110,
            label: 'detail.address',
            placeholder: 'detail.addressPlaceholder',
        );
        $this->country = new ContentArbiterItem(
            slug: 'country',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 120,
            label: 'detail.country',
            placeholder: 'detail.countryPlaceholder',
        );
        $this->email = new ContentArbiterItem(
            slug: 'email',
            allowed: false,
            type: ContentArbiterItemType::Email,
            order: 130,
            label: 'detail.email',
            placeholder: 'detail.emailPlaceholder',
        );
        $this->phone = new ContentArbiterItem(
            slug: 'telephoneNumber',
            allowed: false,
            type: ContentArbiterItemType::Phone,
            order: 140,
            label: 'detail.phone',
            placeholder: 'detail.phonePlaceholder',
            pattern: '^\+?[0-9 ]+$',
        );
        $this->unit = new ContentArbiterItem(
            slug: 'scoutUnit',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 150,
            label: 'detail.unit',
            placeholder: 'detail.unitPlaceholder',
        );
        $this->languages = new ContentArbiterItem(
            slug: 'languages',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 160,
            label: 'detail.language',
            placeholder: 'detail.language-placeholder',
        );
        $this->tshirt = new ContentArbiterItem(
            slug: 'tshirt',
            allowed: false,
            type: ContentArbiterItemType::TshirtComposite,
            order: 170,
            label: 'detail.tshirt',
            placeholder: 'detail.tshirtPlaceholder',
        );
        $this->food = new ContentArbiterItem(
            slug: 'foodPreferences',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 180,
            label: 'detail.foodHeader',
            helpText: 'detail.food-helptext',
        );
        $this->health = new ContentArbiterItem(
            slug: 'healthProblems',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 190,
            label: 'detail.issues',
            placeholder: 'detail.issues-placeholder',
            required: false,
        );
        $this->medicaments = new ContentArbiterItem(
            slug: 'medicaments',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 200,
            label: 'detail.medicaments',
            placeholder: 'detail.medicaments-placeholder',
            required: false,
        );
        $this->psychicalHealth = new ContentArbiterItem(
            slug: 'psychicalHealthProblems',
            allowed: true,
            type: ContentArbiterItemType::Text,
            order: 210,
            label: 'detail.psychicalIssues',
            placeholder: 'detail.psychicalIssues-placeholder',
            required: false,
        );
        $this->emergencyContact = new ContentArbiterItem(
            slug: 'emergencyContact',
            allowed: false,
            type: ContentArbiterItemType::Text,
            order: 220,
            label: 'detail.emergencyContact',
            placeholder: 'detail.emergencyContact-placeholder',
        );
        $this->swimming = new ContentArbiterItem(
            slug: 'swimming',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 230,
            label: 'detail.swimSkill',
            placeholder: 'detail.swimSkillPlaceholder',
            options: ContentArbiterItem::selfMappedOptions(['detail.swimSkillNo', 'detail.swimSkillLess50', 'detail.swimSkillMore50']),
        );
        $this->scarf = new ContentArbiterItem(
            slug: 'scarf',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 240,
            label: 'detail.scarf',
            placeholder: 'detail.scarfPlaceholder',
            options: ['yes' => 'detail.scarfYes', 'no' => 'detail.scarfNo'],
        );
        $this->arrivalDate = new ContentArbiterItem(
            slug: 'arrivalDate',
            allowed: false,
            type: ContentArbiterItemType::Date,
            order: 250,
            label: 'detail.arrivalDate',
            placeholder: 'detail.arrivalDatePlaceholder',
        );
        $this->departureDate = new ContentArbiterItem(
            slug: 'departureDate',
            allowed: false,
            type: ContentArbiterItemType::Date,
            order: 260,
            label: 'detail.departureDate',
            placeholder: 'detail.departureDatePlaceholder',
        );
        $this->driver = new ContentArbiterItem(
            slug: 'driversLicense',
            allowed: false,
            type: ContentArbiterItemType::Select,
            order: 270,
            label: 'detail.driver',
            placeholder: 'detail.driverPlaceholder',
            options: ['dont' => 'detail.driver-dont', 'less 10000 km' => 'detail.driver-less10k', 'more 10000 km' => 'detail.driver-more10k'],
        );
        $this->skills = new ContentArbiterItem(
            slug: 'skills',
            allowed: false,
            type: ContentArbiterItemType::Textarea,
            order: 280,
            label: 'detail.skills',
            placeholder: 'detail.skills-placeholder',
        );
        $this->preferredPosition = new ContentArbiterItem(
            slug: 'preferredPosition',
            allowed: false,
            type: ContentArbiterItemType::Checkbox,
            order: 290,
            label: 'detail.preferredPosition',
            placeholder: 'detail.position-placeholder',
        );
        $this->printedHandbook = new ContentArbiterItem(
            slug: 'printedHandbook',
            allowed: false,
            type: ContentArbiterItemType::Checkbox,
            order: 300,
            label: 'detail.printedHandbook',
            placeholder: 'detail.printedHandbookPlaceholder',
            required: false,
        );
        $this->parentalConsent = new ContentArbiterItem(
            slug: 'parentalConsent',
            allowed: false,
            type: ContentArbiterItemType::File,
            order: 400,
            label: 'detail.parentalConsent',
            placeholder: 'detail.parentalConsentPlaceholder',
            required: false,
            ageGroup: AgeGroup::Under18,
        );
        $this->hospitalConsent = new ContentArbiterItem(
            slug: 'hospitalConsent',
            allowed: false,
            type: ContentArbiterItemType::File,
            order: 410,
            label: 'detail.hospitalConsent',
            placeholder: 'detail.hospitalConsentPlaceholder',
            required: false,
            ageGroup: AgeGroup::Under18,
        );
        $this->childWorkCert = new ContentArbiterItem(
            slug: 'childWorkCert',
            allowed: false,
            type: ContentArbiterItemType::File,
            order: 420,
            label: 'detail.childWorkCert',
            placeholder: 'detail.childWorkCertPlaceholder',
            required: false,
            ageGroup: AgeGroup::Over18,
        );
        $this->adultEventCert = new ContentArbiterItem(
            slug: 'adultEventCert',
            allowed: false,
            type: ContentArbiterItemType::File,
            order: 430,
            label: 'detail.adultEventCert',
            placeholder: 'detail.adultEventCertPlaceholder',
            required: false,
            ageGroup: AgeGroup::Over18,
        );
        $this->notes = new ContentArbiterItem(
            slug: 'notes',
            allowed: true,
            type: ContentArbiterItemType::Textarea,
            order: 500,
            label: 'detail.notice',
            placeholder: 'detail.notice-placeholder',
            required: false,
        );
    }

    /**
     * @return list<ContentArbiterItem>
     */
    public function getAllItems(): array
    {
        $items = [
            $this->contingent,
            $this->patrolName,
            $this->firstName,
            $this->lastName,
            $this->nickname,
            $this->gender,
            $this->birthDate,
            $this->birthPlace,
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
            $this->parentalConsent,
            $this->hospitalConsent,
            $this->childWorkCert,
            $this->adultEventCert,
            $this->notes,
        ];

        usort($items, fn (ContentArbiterItem $a, ContentArbiterItem $b) => $a->order <=> $b->order);

        return $items;
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

        return $items;
    }
}
