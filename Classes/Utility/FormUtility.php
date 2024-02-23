<?php

declare(strict_types=1);

namespace Remind\FormLog\Utility;

use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class FormUtility
{
    public static function getFormElements(
        FormPersistenceManagerInterface $formPersistenceManager,
        string $currentFormIdentifier
    ): array {
        $result = [];

        $forms = $formPersistenceManager->listForms();
        $formDefinition = current(array_filter($forms, function (array $form) use ($currentFormIdentifier) {
            return $form['identifier'] === $currentFormIdentifier;
        }));
        if ($formDefinition) {
            $form = $formPersistenceManager->load($formDefinition['persistenceIdentifier']);

            foreach ($form['renderables'] as $element) {
                self::getFormElement($element, $result);
            }
        }

        return $result;
    }

    private static function getFormElement(array $element, array &$result): void
    {

        if (isset($element['renderables'])) {
            foreach ($element['renderables'] as $childElement) {
                self::getFormElement($childElement, $result);
            }
        } else {
            $result[$element['identifier']] = [
                'type' => $element['type'],
                'label' => $element['label'],
                'identifier' => $element['identifier'],
                'properties' => $element['properties'] ?? null,
            ];
        }
    }
}
