<?php

use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

class Page2MultiForm extends MultiForm
{

    public static $start_step = 'Page2PersonalDetailsFormStep';

    public function finish(array $data, Form $form): HTTPResponse
    {
        parent::finish($data, $form);
        $steps = DataObject::get('MultiFormStep', "SessionID = {$this->session->ID}");
        if ($steps) {
            foreach ($steps as $step) {
                if ($step->ClassName == 'Page2PersonalDetailsFormStep') {
                    $member = new Member();
                    $data = $step->loadData();
                    if ($data) {
                        $member->update($data);
                        $member->write();
                    }
                }

                if ($step->ClassName == 'Page2OrganisationDetailsFormStep') {
                    $organisation = new Organisation();
                    $data = $step->loadData();
                    if ($data) {
                        $organisation->update($data);
                        if ($member && $member->ID) {
                            $organisation->MemberID = $member->ID;
                        }
                        $organisation->write();
                    }
                }

                // Debug::show($step->loadData()); // Shows the step data (unserialized by loadData)
            }
        }
        $controller = $this->getController();
        return $controller->redirect($controller->Link() . 'finished');
    }
}

class Page2PersonalDetailsFormStep extends MultiFormStep
{

    public static $next_steps = 'Page2OrganisationDetailsFormStep';

    public function getFields()
    {
        return new FieldList(
            new TextField('FirstName', 'First name'),
            new TextField('Surname', 'Surname')
        );
    }
}

class Page2OrganisationDetailsFormStep extends MultiFormStep
{

    public static $is_final_step = true;

    public function getFields()
    {
        return new FieldList(
            new TextField('OrganisationName', 'Organisation Name')
        );
    }
}
