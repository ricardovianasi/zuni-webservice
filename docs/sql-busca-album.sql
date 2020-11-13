SELECT A.* FROM albums A
LEFT JOIN `share` SH on SH.id_album = A.id
LEFT JOIN share_users SU on SU.id_share = SH.id
LEFT JOIN share_groups SG on SG.id_share = SH.id
LEFT JOIN users_groups USG on USG.id_group = SG.id_group 

where 
	A.visibility = 0
	OR (A.visibility = 1 AND A.id_user = 4)
	OR SU.id_user = 4
	OR USG.id_user = 4
	
GROUP BY A.id