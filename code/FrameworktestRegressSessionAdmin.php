<?php

use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\Form;


/**
 * Starts a test session with various configurations set in session.
 * These configurations are assumed to be evaluated in mysite/_config.php,
 * with custom switches for the different options.
 */
class FrameworktestRegressSessionAdmin extends Controller
{

    protected $template = 'BlankPage';

    public function init()
    {
        parent::init();

        if (!Permission::check('ADMIN')) {
            return Security::permissionFailure($this);
        }
    }

    public function Link($action = null)
    {
        return Controller::join_links('dev', 'regress', $action);
    }

    public function Form()
    {
        $isRunning = (Session::get('db'));

        if ($isRunning) {
            $actions = new FieldList(
                new FormAction('endsession', 'End Session')
            );
        } else {
            $actions = new FieldList(
                new FormAction('startsession', 'Start Session')
            );
        }

        $form = new Form(
            $this,
            'SilverStripe\\Forms\\Form',
            new FieldList(
                new HeaderField('Header1', ($isRunning) ? 'Session is already running' : 'Start new regress session'),
                new LiteralField('Lit1',
                    '<p>Use this form to set configuration prior to starting a <a href="http://regress.silverstripe.com">regress.silverstripe.com</a> test session (manual testing).</p>'
                ),
                $dbField = new DropdownField(
                    'db',
                    'Database',
                    array(
                        'mysql' => 'MySQL',
                        'postgresql' => 'Postgres',
                        'mssql' => 'MSSQL',
                        'sqlite3' => 'SQLite3',
                    ),
                    Session::get('db')
                ),
                $chkField = new CheckboxField(
                    'enabletranslatable',
                    'Translatable?',
                    Session::get('enabletranslatable')
                )
            ),
            $actions
        );
        $dbField->setHasEmptyDefault(false);

        if ($isRunning) {
            foreach ($form->Fields() as $field) {
                $form->Fields()->replaceField($field->Name(),
                    $field->performReadonlyTransformation()
                );
            }
        }

        return $form;
    }

    public function startsession(array $data, Form $form): HTTPResponse
    {
        Session::set('enabletranslatable', (isset($data['enabletranslatable'])) ? $data['enabletranslatable'] : null);
        Session::set('db', $data['db']);

        return $this->redirect('dev/build/?BackURL=admin');
    }

    public function endsession()
    {
        Session::set('enabletranslatable', null);
        Session::set('db', null);

        return $this->redirectBack();
    }
}
