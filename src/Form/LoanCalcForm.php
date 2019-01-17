<?php

namespace Drupal\loan_calc\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;

/**
 * Provides a loan calculator form.
 */
class LoanCalcForm extends FormBase {

  use LoanCalcFormTrait;

  protected $state;

  /**
   * LoanCalcForm constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state storage service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container) {
    $form = new static(
      $container->get('state')
    );
    $form->setStringTranslation($container->get('string_translation'));
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'loan_calc_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaults = $this->state->get('loan_calc') ??
    $this->config('loan_calc.settings')
      ->get('loan_calc');

    $form = $this->getFormDefinition($defaults);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Calculate'),
      '#button_type' => 'primary',
    ];
    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => [
        '::resetFormHandler',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fields = array_keys(
      $this->getFormDefinition()
    );

    foreach ($fields as $field) {
      $values[$field] = $form_state->getValue($field);
    }

    if (!empty($values)) {
      $this->state->set('loan_calc', $values);
      $this->logger('loan_calc')->info(
        'Loan Calculator values entered: <br><pre>@values</pre>',
        ['@values' => print_r($values, TRUE)]
      );
    }

    $form_state->setRedirect('loan_calc.page');
  }

  /**
   * Reset form values to defaults.
   */
  public function resetFormHandler(array &$form, FormStateInterface $form_state) {
    $this->state->delete('loan_calc');
    $this->logger('loan_calc')
      ->notice('Loan Calculator reset to defaults.');
  }

}
