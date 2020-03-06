<?php
namespace TF\StoreHours\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use TF\StoreHours\Block\Text;
use TF\StoreHours\Time\IntlTimeFormatter;

/**
 * Class Hours
 */
class Hours extends AbstractFieldArray
{
    /**
     * @var Text
     */
    private $dayRenderer;

    private $timeFormatter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param IntlTimeFormatter $timeFormatter
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        IntlTimeFormatter $timeFormatter
    ) {
        $this->timeFormatter = $timeFormatter;
        parent::__construct($context, $data);
    }

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('day', [
            'label' => __('Day'),
            'renderer' => $this->getDayRenderer()
        ]);

        $this->addColumn('opening_time', ['label' => __('Opening Time')]);
        $this->addColumn('closing_time', ['label' => __('Closing Time')]);

        $this->_addAfter = false;
        $this->_template = 'TF_StoreHours::system/config/form/field/hours.phtml';
    }

    /**
     * Format timestamps based on locale and timezone.
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $openingTime = $row->getOpeningTime();
        $closingTime = $row->getClosingTime();
        $columnValues = $row->getColumnValues();
        $day = $row->getDay();

        if ($openingTime || $openingTime === 0) {
            $formattedOpeningTime = $this->timeFormatter->formatTime($openingTime);
            $columnValues[$day.'_opening_time'] = $formattedOpeningTime;
            $row->setColumnValues($columnValues);
            $row->setOpeningTime($formattedOpeningTime);
        }

        if ($closingTime || $closingTime === 0) {
            $formattedClosingTime = $this->timeFormatter->formatTime($closingTime);
            $columnValues[$day.'_closing_time'] = $formattedClosingTime;
            $row->setColumnValues($columnValues);
            $row->setClosingTime($formattedClosingTime);
        }
    }

    /**
     * @return Text
     * @throws LocalizedException
     */
    private function getDayRenderer()
    {
        if (!$this->dayRenderer) {
            $this->dayRenderer = $this->getLayout()->createBlock(
                Text::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->dayRenderer;
    }
}
