select I.* from images I

/*album*/
inner join albums AL on AL.id = I.id_album
left join albums_tags ALTA on ALTA.id_album = AL.id

/*usuario*/
inner join users USER on USER.id = I.id_user
left join `share` SHA on SHA.id_album = AL.id
left join share_users SHUS on SHUS.id_share = SHA.id
left join share_groups SHUG on SHUG.id_share = SHA.id
left join users_groups USGR on USGR.id_group = SHUG.id_group

/*tags*/
left join images_tags IMTA on IMTA.id_image = I.id
left join tags TAG on TAG.id = IMTA.id_tag OR TAG.id = ALTA.id_tag

/*localização*/
left join images_locations IMLO on IMLO.images_id = I.id
left join locations LOC on LOC.id = IMLO.locations_id

where (
		I.description like '%teste%'
		OR I.owner like '%teste%'
		OR TAG.tag like '%teste%'
		OR LOC.name like '%teste%'
		
		OR AL.name like '%teste%'
		OR AL.description like '%teste%'
	)
	AND (
		USER.id = 3
		OR AL.visibility = 0
		OR (AL.visibility = 1 AND AL.id_user = 3)
		OR SHUS.id_user = 3
		OR USGR.id_user = 3
	)
	/*AND AL.id = 8*/

GROUP BY I.id
