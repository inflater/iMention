<?php
/* 
	iMention - 언급 플러그인
	본플러그인의 수정은 허용하나, 재배포 및 무단배포는 금지합니다.

	Copyright 2015-2016. 인플레터(egmzkdhtm@naver.com) in HONEY Server All Rights Reserved.
*/
namespace inflater\iMention;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use pocketmine\Player;

class iMention extends PluginBase implements Listener{
	protected $config, $EconomyAPI;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
		$this->getLogger()->info(TextFormat::DARK_GREEN . "iMention v{$this->getDescription()->getVersion()} - 언급 플러그인");
		$this->loadData();
	}

	public function onPlayerChat(PlayerChatEvent $ev){
		if(!$ev->getPlayer()->hasPermission('iMention.mention')) { return; }
		if(preg_match_all('/@[a-zA-Z0-9_]+/', $ev->getMessage(), $msg)) {
			for($i = 0; $i<count($msg[0]); $i++) {
				$target = $this->getServer()->getPlayer(str_replace('@', '', $msg[0][$i]));
				if($target instanceof Player){
					$message = str_replace(
					['{nickname}', '{economy-money}', '{displayName}', '{x}', '{y}', '{z}'],
					[$target->getName(), $this->EconomyAPI->myMoney($target), $target->getDisplayName(), $target->getFloorX(), $target->getFloorY(), $target->getFloorZ()],
					$this->config['format']);
					$ev->setmessage(str_replace($msg[0][$i], $message, $ev->getMessage()));
					if($this->config['notice']!=="")
						$target->sendMessage(TextFormat::AQUA . str_replace('{player}', $ev->getPlayer()->getName(), $this->config['notice']));
				}
			}
		}
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $ev){
		if(!$ev->getPlayer()->hasPermission('iMention.command')) { return; }
		if(preg_match_all('/@[a-zA-Z0-9_]+/', $ev->getMessage(), $msg)) {
			for($i = 0; $i<count($msg[0]); $i++) {
				$target = $this->getServer()->getPlayer(str_replace('@', '', $msg[0][$i]));
				if($target instanceof Player){
					$ev->setMessage(str_replace($msg[0][$i], $target->getName(), $ev->getMessage()));
				}
			}

		}
	}

	public function checkUpdate($name, $version){
		$plugin = json_decode(Utils::getUrl("http://hn.pe.kr/plugin/versionCheck.php?pluginName={$name}&version={$version}"), true);
		if($plugin['update']) { $this->getLogger()->notice("iMention 플러그인의 최신버전이 있습니다. (v{$plugin['latest-version']})"); }
		else{ $this->getLogger()->notice("현재 최신버전의 iMention 플러그인을 사용중입니다."); }
	}

	public function loadData(){
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml", false);
		$this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML))->getAll();
		if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) {
			$this->EconomyAPI = \onebone\economyapi\EconomyAPI::getInstance();
		}
	}
}
?>