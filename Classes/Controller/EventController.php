<?php
	namespace Slub\SlubEvents\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2014 Alexander Bigga <alexander.bigga@slub-dresden.de>, SLUB Dresden
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package slub_events
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

	use TYPO3\CMS\Core\Utility\GeneralUtility;
	use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class EventController extends AbstractController {


	/**
	 * Initializes the current action
	 *
	 * idea from tx_news extension
	 *
	 * @return void
	 */
	public function initializeAction() {

		// Only do this in Frontend Context
		if (!empty($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
			// We only want to set the tag once in one request, so we have to cache that statically if it has been done
			static $cacheTagsSet = FALSE;

			/** @var $typoScriptFrontendController \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController  */
			$typoScriptFrontendController = $GLOBALS['TSFE'];
			if (!$cacheTagsSet) {
				$typoScriptFrontendController->addCacheTags(array(1 => 'tx_slubevents_' . $this->settings['storagePid']));
				$cacheTagsSet = TRUE;
			}
			$this->typoScriptFrontendController = $typoScriptFrontendController;
		}
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {

		if (!empty($this->settings['categorySelection'])) {
			$categoriesIds = GeneralUtility::intExplode(',', $this->settings['categorySelection'], TRUE);

			if ($this->settings['categorySelectionRecursive']) {
				// add somehow the other categories...
				foreach ($categoriesIds as $category) {
					$foundRecusiveCategories = $this->categoryRepository->findAllChildCategories($category);
					if (count($foundRecusiveCategories) > 0)
						$categoriesIds = array_merge($foundRecusiveCategories, $categoriesIds);
				}
			}
			$this->settings['categoryList'] = $categoriesIds;
		}

		if (!empty($this->settings['disciplineSelection'])) {
			$disciplineIds = GeneralUtility::intExplode(',', $this->settings['disciplineSelection'], TRUE);

			if ($this->settings['disciplineSelectionRecursive']) {
				// add somehow the other categories...
				foreach ($disciplineIds as $discipline) {
					$foundRecusiveDisciplines = $this->disciplineRepository->findAllChildDisciplines($discipline);
					if (count($foundRecusiveDisciplines) > 0)
						$disciplineIds = array_merge($foundRecusiveDisciplines, $disciplineIds);
				}
			}
			$this->settings['disciplineList'] = $disciplineIds;
		}

		$events = $this->eventRepository->findAllBySettings($this->settings);

		$this->view->assign('events', $events);
	}

	/**
	 * action initializeShow
	 *
	 * @return void
	 */
//	public function initializeShowAction() {
//
//		$eventId = $this->getParametersSafely('event');
//		$event = NULL;
//
//		if ($eventId != NULL)
//			$event = $this->eventRepository->findByUid($eventId);
//
//		if ($event === NULL)
//			$this->redirect('showNotFound');
//	}

	/**
	 * action show
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $event
	 * @ignorevalidation $event
	 * @return void
	 */
	public function showAction(\Slub\SlubEvents\Domain\Model\Event $event = NULL) {

		if ($event !== NULL) {
			// fill registers to be used in ts
			$cObj = GeneralUtility::makeInstance('tslib_cObj');
			$cObj->LOAD_REGISTER(
				array(
					'eventPageTitle' => LocalizationUtility::translate('tx_slubevents_domain_model_event', 'slub_events') . ': "' . $event->getTitle() . '" - ' . strftime('%a, %x %H:%M', $event->getStartDateTime()->getTimeStamp()),
				), 'LOAD_REGISTER');
		}

		$this->view->assign('event', $event);
	}

	/**
	 * action showNotfound
	 *
	 * @return void
	 */
	public function showNotFoundAction() {

	}

	/**
	 * action new
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $newEvent
	 * @ignorevalidation $newEvent
	 * @return void
	 */
	public function newAction(\Slub\SlubEvents\Domain\Model\Event $newEvent = NULL) {
			$this->view->assign('newEvent', $newEvent);
	}

	/**
	 * action create
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $newEvent
	 * @return void
	 */
	public function createAction(\Slub\SlubEvents\Domain\Model\Event $newEvent) {
		$this->eventRepository->add($newEvent);
		$this->flashMessageContainer->add('Your new Event was created.');
		$this->redirect('list');
	}

	/**
	 * action edit
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $event
	 * @ignorevalidation $event
	 * @return void
	 */
	public function editAction(\Slub\SlubEvents\Domain\Model\Event $event) {
		$this->view->assign('event', $event);
	}

	/**
	 * action update
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $event
	 * @return void
	 */
	public function updateAction(\Slub\SlubEvents\Domain\Model\Event $event) {
		$this->eventRepository->update($event);
		$this->flashMessageContainer->add('Your Event was updated.');
		$this->redirect('list');
	}

	/**
	 * action delete
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $event
	 * @return void
	 */
	public function deleteAction(\Slub\SlubEvents\Domain\Model\Event $event) {
		$this->eventRepository->remove($event);
		$this->flashMessageContainer->add('Your Event was removed.');
		$this->redirect('list');
	}

	/**
	 * action listOwn
	 *
	 * @return void
	 */
	public function listOwnAction() {

		// + the user is logged in
		// + the username == customerid
		$subscribers = $this->subscriberRepository->findAllByFeuser();
debug(count($subscribers));
		$events = $this->eventRepository->findAllBySubscriber($subscribers);

		$this->view->assign('subscribers', $subscribers);
		$this->view->assign('events', $events);
	}

	/**
	 * action beList
	 *
	 * @return void
	 */
	public function beListAction() {

		// get data from BE session
		$sessionData = $GLOBALS['BE_USER']->getSessionData('tx_slubevents');
		// get search parameters from BE user configuration
		$ucData = $GLOBALS['BE_USER']->uc['moduleData']['slubevents'];

		// -----------------------------------------
		// get search parameters from POST variables
		// -----------------------------------------
		$searchParameter = $this->getParametersSafely('searchParameter');
		if (is_array($searchParameter)) {
			$ucData['searchParameter'] = $searchParameter;
			$sessionData['selectedStartDateStamp'] = $searchParameter['selectedStartDateStamp'];
			$GLOBALS['BE_USER']->uc['moduleData']['slubevents'] = $ucData;
			$GLOBALS['BE_USER']->writeUC($GLOBALS['BE_USER']->uc);
			// save session data
			$GLOBALS['BE_USER']->setAndSaveSessionData('tx_slubevents', $sessionData);
		} else {
			// no POST vars --> take BE user configuration
			$searchParameter = $ucData['searchParameter'];
		}

		// set the startDateStamp
		// startDateStamp is saved in session data NOT in user data
		if (empty($selectedStartDateStamp)) {
			if (!empty($sessionData['selectedStartDateStamp']))
				$selectedStartDateStamp = $sessionData['selectedStartDateStamp'];
			else
				$selectedStartDateStamp = date('d-m-Y');
		}

		// get the categories
		$categories = $this->categoryRepository->findAllTree();
		// get all contacts
		$contacts = $this->contactRepository->findAllSorted();

		// check which categories have been selected
		if (is_array($searchParameter['selectedCategories'])) {
			$this->view->assign('selectedCategories', $searchParameter['selectedCategories']);
		}
		else {
			// if no category selection in user settings present --> look for the root categories
			if (! is_array($searchParameter['category']))
				foreach ($categories as $uid => $category)
					$searchParameter['category'][$uid] = $uid;
			$this->view->assign('categoriesSelected', $searchParameter['category']);
		}

		// check which contacts have been selected
		if (is_array($searchParameter['selectedContacts'])) {
			$this->view->assign('selectedContacts', $searchParameter['selectedContacts']);
		}
		else {
			// if no contacts selection in user settings present --> look for the root categories
			if (! is_array($searchParameter['contacts']))
				foreach ($contacts as $uid => $contact)
					$searchParameter['contacts'][$uid] = $contact->getUid();
			$this->view->assign('contactsSelected', $searchParameter['contacts']);
		}
		$this->view->assign('selectedStartDateStamp', $selectedStartDateStamp);
	//~ t3lib_utility_Debug::debug($searchParameter['contacts'], 'selectedStartDateStamp... ');

		// get the events to show
		if (is_array($searchParameter['category']))
			$events = $this->eventRepository->findAllByCategoriesAndDate($searchParameter['category'], strtotime($selectedStartDateStamp), $searchParameter['searchString'], $searchParameter['contacts']);

		$this->view->assign('searchString', $searchParameter['searchString']);
		$this->view->assign('categories', $categories);
		$this->view->assign('events', $events);
		$this->view->assign('contacts', $contacts);

	}

	/**
	 * action beCopy
	 *
	 * @param \Slub\SlubEvents\Domain\Model\Event $event
	 * @ignorevalidation $event
	 * @return void
	 */
	public function beCopyAction($event) {

		$availableProperties = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getGettablePropertyNames($event);
		$newEvent =  $this->objectManager->create('\Slub\SlubEvents\Domain\Model\Event');

		foreach ($availableProperties as $propertyName) {
			if (\TYPO3\CMS\Extbase\Reflection\ObjectAccess::isPropertySettable($newEvent, $propertyName)
				&& !in_array($propertyName, array('uid','pid','subscribers', 'cancelled', 'subEndDateTime','subEndDateInfoSent','categories', 'discipline'))) {

				$propertyValue = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($event, $propertyName);
				// special handling for onlinesurvey field to remove trailing timestamp with sent date
				if ($propertyName == 'onlinesurvey') {
					$propertyValue = substr($propertyValue, 0, strpos($propertyValue, '|'));
				}
				\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($newEvent, $propertyName, $propertyValue);
			}
		}

		foreach ($event->getCategories() as $cat) {
			$newEvent->addCategory($cat);
		}

		foreach ($event->getDiscipline() as $discipline) {
			$newEvent->addDiscipline($discipline);
		}

		if ($event->getGeniusBar())
			$newEvent->setTitle('Wissensbar ' . $newEvent->getContact()->getName());
		else
			$newEvent->setTitle($newEvent->getTitle());

		$newEvent->setHidden(TRUE);

		$this->eventRepository->add($newEvent);

		$this->flashMessageContainer->add('Die Veranstaltung '.$newEvent->getTitle().' wurde kopiert.');
		$this->redirect('beList');
	}

	/**
	 * action listMonth
	 *
	 * @return void
	 */
	public function listMonthAction() {

		if (!empty($this->settings['categorySelection'])) {
			$categoriesIds = GeneralUtility::intExplode(',', $this->settings['categorySelection'], TRUE);

			if ($this->settings['categorySelectionRecursive']) {
				// add somehow the other categories...
				foreach ($categoriesIds as $category) {
					$foundRecusiveCategories = $this->categoryRepository->findAllChildCategories($category);
					if (count($foundRecusiveCategories) > 0)
						$categoriesIds = array_merge($foundRecusiveCategories, $categoriesIds);
				}
			}
			$this->settings['categoryList'] = $categoriesIds;
			$categories = $this->categoryRepository->findAllByUidsTree($this->settings['categoryList']);
		}

		if (!empty($this->settings['disciplineSelection'])) {
			$disciplineIds = GeneralUtility::intExplode(',', $this->settings['disciplineSelection'], TRUE);

			if ($this->settings['disciplineSelectionRecursive']) {
				// add somehow the other categories...
				foreach ($disciplineIds as $discipline) {
					$foundRecusiveDisciplines = $this->disciplineRepository->findAllChildDisciplines($discipline);
					if (count($foundRecusiveDisciplines) > 0)
						$disciplineIds = array_merge($foundRecusiveDisciplines, $disciplineIds);
				}
			}
			$this->settings['disciplineList'] = $disciplineIds;
			$disciplines = $this->disciplineRepository->findAllByUidsTree($this->settings['disciplineList']);
		}

		$this->view->assign('categories', $categories);
		$this->view->assign('disciplines', $disciplines);
		$this->view->assign('categoriesIds', explode(',', $this->settings['categorySelection']));
		$this->view->assign('disciplinesIds', explode(',', $this->settings['disciplineSelection']));
	}


	/**
	 * action errorAction
	 *
	 * @return void
	 */
	public function errorAction() {

	}


	/**
	 * action ajax
	 *
	 * EXPERIMENTAL!!
	 *
	 * @return void
	 */
	public function ajaxAction() {

		$events = $this->eventRepository->findAllBySettings(array(
			'categoryList' => GeneralUtility::intExplode(',', $_GET['categories'], TRUE),
			'disciplineList' => GeneralUtility::intExplode(',', $_GET['disciplines'], TRUE),
			'startTimestamp' => $_GET['start'],
			'stopTimestamp' => $_GET['stop'],
			'showPastEvents' => TRUE)
		);

		$cObj = $this->configurationManager->getContentObject();
		foreach ($events as $event) {

			$foundevent = array();

			$foundevent['id'] = $event->getUid();
			$foundevent['title'] = $event->getTitle();
			$foundevent['teaser'] = $event->getTeaser();
			$foundevent['start'] = $event->getStartDateTime()->format('Y-m-d H:i:s');
			foreach ($event->getCategories() as $cat) {
				$foundevent['className'] .= ' slubevents-category-' . $cat->getUid();
			}

			//~ $foundevent['className'] = 'slubevents-category-' . $event->getCategories(); // $_GET['categories'];
			if ($event->getEndDateTime() instanceof DateTime)
				$foundevent['end'] = $event->getEndDateTime()->format('Y-m-d H:i:s');

			$conf = array(
				// Link to current page
				'parameter' => $_GET['detailPid'],
				// Set additional parameters
				'additionalParams' => '&type=0&tx_slubevents_eventlist%5Bevent%5D='.$event->getUid().'&tx_slubevents_eventlist%5Baction%5D=show',
				// We must add cHash because we use parameters
				'useCacheHash' => 1,
				// We want link only
				'returnLast' => 'url',
			);
			$url = $cObj->typoLink('', $conf);
			//~
			$foundevent['url'] = $url;

			if ($event->getAllDay())
				$foundevent['allDay'] = true;
			else
				$foundevent['allDay'] = false;

			// how many free places are available?
			$freePlaces = ($event->getMaxSubscriber() - $this->subscriberRepository->countAllByEvent($event));
			if ($freePlaces <= 0)
				$foundevent['freePlaces'] = 0;
			else if ($freePlaces == 1)
				$foundevent['freePlaces'] = Tx_Extbase_Utility_Localization::translate('tx_slubevents_domain_model_event.oneFreePlace', 'slub_events');
			else
				$foundevent['freePlaces'] = ($event->getMaxSubscriber() - $this->subscriberRepository->countAllByEvent($event)) . ' ' . Tx_Extbase_Utility_Localization::translate('tx_slubevents_domain_model_event.freeplaces', 'slub_events');

			// set special css class if subscription is NOT possible
			$noSubscription = FALSE;
			// limit reached already --> overbooked
			if ($this->subscriberRepository->countAllByEvent($event) >= $event->getMaxSubscriber()) {
				$noSubscription = TRUE;
			}
			// event is cancelled
			if ($event->getCancelled()) {
				$noSubscription = TRUE;
			}
			// deadline reached....
			if (is_object($event->getSubEndDateTime())) {
				if ($event->getSubEndDateTime()->getTimestamp() < time()) {
					$noSubscription = TRUE;
				}
			}
			if ($noSubscription) {
				$foundevent['className'] .= ' no_subscription';
			}

			$jsonevent[] = $foundevent;
		}
		return json_encode($jsonevent);
	}

}

?>
