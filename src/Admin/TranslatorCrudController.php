<?php declare(strict_types=1);

namespace Danilovl\TranslatorBundle\Admin;

use Danilovl\TranslatorBundle\Entity\Translator;
use Danilovl\TranslatorBundle\Util\TranslatorConfigurationUtil;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    TextareaField,
    TextField,
    FormField,
    ChoiceField,
    IntegerField,
    DateTimeField};

class TranslatorCrudController extends AbstractCrudController
{
    public function __construct(private readonly TranslatorConfigurationUtil $translatorConfigurationUtil) {}

    public static function getEntityFqcn(): string
    {
        return Translator::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Translator')
            ->setEntityLabelInPlural('Translators')
            ->setSearchFields(['id', 'locale', 'domain', 'key', 'value'])
            ->setDefaultSort(['id' => Criteria::DESC]);
    }

    public function configureFields(string $pageName): iterable
    {
        $locales = $this->translatorConfigurationUtil->getLocales();
        $localesChoices = array_combine($locales, $locales);

        $domains = $this->translatorConfigurationUtil->getDomains();
        $domainsChoices = array_combine($domains, $domains);

        yield FormField::addPanel('Translator Information');
        yield IntegerField::new('id', 'ID')->onlyOnIndex();
        yield ChoiceField::new('locale')->setChoices($localesChoices);
        yield ChoiceField::new('domain')->setChoices($domainsChoices);
        yield TextField::new('key');
        yield TextareaField::new('value');
        yield DateTimeField::new('createdAt')->hideOnIndex()->setDisabled();
        yield DateTimeField::new('updatedAt')->hideOnIndex()->setDisabled();
    }
}
