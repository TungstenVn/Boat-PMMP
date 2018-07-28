<?php

namespace onebone\boat\listener;

use onebone\boat\entity\Boat as BoatEntity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\{
	InteractPacket, MovePlayerPacket, SetEntityLinkPacket
};
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Server;

class EventListener implements Listener{
	private $riding = [];

	public function onQuit(PlayerQuitEvent $event) : void{
		if(isset($this->riding[$event->getPlayer()->getName()])){
			unset($this->riding[$event->getPlayer()->getName()]);
		}
	}

	public function onPacketReceived(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		if($packet instanceof InteractPacket){
			$boat = $player->getLevel()->getEntity($packet->target);
			if($boat instanceof BoatEntity){
				if($packet->action === 3){
					$pk = new SetEntityLinkPacket();
					$pk->link = new EntityLink($player->getId(), $boat->getId(), EntityLink::TYPE_REMOVE);
					Server::getInstance()->broadcastPacket($player->getViewers(), $pk);
					$player->dataPacket($pk);

					if(isset($this->riding[$event->getPlayer()->getName()])){
						unset($this->riding[$event->getPlayer()->getName()]);
					}
				}
			}
		}elseif($packet instanceof MovePlayerPacket){
			if(isset($this->riding[$player->getName()])){
				$boat = $player->getLevel()->getEntity($this->riding[$player->getName()]);
				if($boat instanceof BoatEntity){
					$boat->teleport($packet->position);
				}
			}
		}
	}
}