<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Keyword\Action;

use Keyword\Event\KeywordAssociationEvent;
use Keyword\Event\KeywordDeleteEvent;
use Keyword\Event\KeywordEvents;

use Keyword\Event\KeywordToggleVisibilityEvent;
use Keyword\Event\KeywordUpdateEvent;
use Keyword\Model\ContentAssociatedKeyword;
use Keyword\Model\ContentAssociatedKeywordQuery;

use Keyword\Model\FolderAssociatedKeyword;
use Keyword\Model\FolderAssociatedKeywordQuery;

use Keyword\Model\KeywordQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\UpdatePositionEvent;

/**
 *
 * keyword class where all actions are managed
 *
 * Class Keyword
 * @package Keyword\Action
 * @author Michaël Espeche <mespeche@openstudio.fr>
 */
class Keyword extends BaseAction implements EventSubscriberInterface
{

    public function updateKeywordFolderAssociation(KeywordAssociationEvent $event)
    {
        // Folder to associate
        $folder = $event->getFolder();

        // Keyword to save to this folder
        $keywordListToSave = $event->getKeywordList();

        // Delete all association to this folder
        FolderAssociatedKeywordQuery::create()
            ->filterByFolderId($folder->getId())
            ->delete();

        // Create all associations to this folder
        foreach ($keywordListToSave as $keywordId) {

            $keywordFolderAssociation = new FolderAssociatedKeyword();
            $keywordFolderAssociation
                ->setFolderId($folder->getId())
                ->setKeywordId($keywordId)
                ->save();

        }

    }

    public function updateKeywordContentAssociation(KeywordAssociationEvent $event)
    {
        // Content to associate
        $content = $event->getContent();

        // Keyword to save to this content
        $keywordListToSave = $event->getKeywordList();

        // Delete all association to this content
        ContentAssociatedKeywordQuery::create()
            ->filterByContentId($content->getId())
            ->delete();

        // Create all associations to this folder
        foreach ($keywordListToSave as $keywordId) {

            $keywordFolderAssociation = new ContentAssociatedKeyword();
            $keywordFolderAssociation
                ->setContentId($content->getId())
                ->setKeywordId($keywordId)
                ->save();

        }

    }

    public function updateKeywordPosition(UpdatePositionEvent $event)
    {
        if (null !== $keyword = KeywordQuery::create()->findPk($event->getObjectId())) {
            $keyword->setDispatcher($this->getDispatcher());

            switch ($event->getMode()) {
                case UpdatePositionEvent::POSITION_ABSOLUTE:
                    $keyword->changeAbsolutePosition($event->getPosition());
                    break;
                case UpdatePositionEvent::POSITION_DOWN:
                    $keyword->movePositionDown();
                    break;
                case UpdatePositionEvent::POSITION_UP:
                    $keyword->movePositionUp();
                    break;
            }
        }
    }

    public function createKeyword(KeywordEvents $event)
    {
        $keyword = new \Keyword\Model\Keyword();

        $keyword
            ->setLocale($event->getLocale())
            ->setTitle($event->getTitle())
            ->setCode($event->getCode())
            ->setVisible($event->getVisible())
            ->create()
        ;

        $event->setKeyword($keyword);
    }

    public function deleteKeyword(KeywordDeleteEvent $event)
    {
        if (null !== $keyword = KeywordQuery::create()->findPk($event->getKeywordId())) {

            $keyword->setDispatcher($this->getDispatcher())
                ->delete();

            $event->setKeyword($keyword);
        }
    }

    /**
     * process update keyword
     *
     * @param KeywordUpdateEvent $event
     */
    public function updateKeyword(KeywordUpdateEvent $event)
    {
        if (null !== $keyword = KeywordQuery::create()->findPk($event->getKeywordId())) {
            $keyword->setDispatcher($this->getDispatcher());

            $keyword
                ->setVisible($event->getVisible())
                ->setLocale($event->getLocale())
                ->setTitle($event->getTitle())
                ->setCode($event->getCode())
                ->setDescription($event->getDescription())
                ->setChapo($event->getChapo())
                ->setPostscriptum($event->getPostscriptum())
                ->save()
            ;

            $event->setKeyword($keyword);
        }
    }

    public function toggleVisibilityKeyword(KeywordToggleVisibilityEvent $event)
    {
        $keyword = $event->getKeyword();

        $keyword
            ->setDispatcher($this->getDispatcher())
            ->setVisible(!$keyword->getVisible())
            ->save();

        $event->setKeyword($keyword);

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {

        return array(
            KeywordEvents::KEYWORD_UPDATE_FOLDER_ASSOCIATION    => array('updateKeywordFolderAssociation', 128),
            KeywordEvents::KEYWORD_UPDATE_CONTENT_ASSOCIATION   => array('updateKeywordContentAssociation', 128),
            KeywordEvents::KEYWORD_UPDATE_POSITION              => array('updateKeywordPosition', 128),
            KeywordEvents::KEYWORD_CREATE                       => array('createKeyword', 128),
            KeywordEvents::KEYWORD_UPDATE                       => array('updateKeyword', 128),
            KeywordEvents::KEYWORD_DELETE                       => array('deleteKeyword', 128),
            KeywordEvents::KEYWORD_TOGGLE_VISIBILITY            => array('toggleVisibilityKeyword', 128)
        );
    }
}