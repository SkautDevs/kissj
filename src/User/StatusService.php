<?php

namespace kissj\User;

class StatusService {
	
	public function getHelpForRole(?Role $role): ?string {
		if (is_null($role)) {
			return null;
		}
		switch ($role->name) {
			case 'admin':
				{
					return null;
				}
			
			default:
				{
					switch ($role->status) {
						case 'open':
							{
								switch ($role->name) {
									case 'patrol-leader':
										return 'Vyplň všechny údaje o sobě, přidej správný počet účastníků, vyplň údaje i u nich a potom dole klikni na tlačítko Uzavřít registraci.';
									case 'ist':
										return 'Vyplň všechny údaje o sobě a potom dole klikni na Uzavřít registraci.';
									default:
										throw new \Exception('Unknown/unimplemented name of role: '.$role->name);
								}
								
							}
						case 'closed':
							return 'Tvoje registrace čeká na schválení. Po schválení ti pošleme email s platebními údaji. Pokud to trvá moc dlouho, ozvi se nám na mail cej2018@skaut.cz';
						case 'approved':
							return 'Tvoje registrace byla přijata! Teď nadchází placení. Tvoje platební údaje jsou níže.';
						case 'paid':
							return 'Registraci máš vyplněnou, odevzdanou, přijatou i zaplacenou. Těšíme se na tebe na akci!';
						default:
							throw new \Exception('Unknown role: '.$role->status);
					}
				}
		}
	}
}