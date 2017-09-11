--[[

The GetRankChange function will get the next rank if a player's role is incremented change times.
For example, if I wanted to get the rank to promote someone once, I would do:

GetRankChange(plr,1)

and demoting with:

GetRankChange(plr,-1)

The GetRankInGroup function uses a proxy to get the players rank from onlne.
The reason it does this is because ROBLOX's GetRankInGroup caches.
So in case you're making multiple rank changes you'll still get the correct rank.

**HttpService must be enabled**

]]

local group = 2735634
GetRankInGroup = function(userId,group)
    return tonumber(game:GetService'HttpService':GetAsync(string.format('http://rprxy.xyz/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=%d&groupid=%d',userId,group)):match'<.*>(.*)<.*>')
end
GetRankChange = function(plr,change)
    local userRank = GetRankInGroup(plr.userId,group)
    local ranks = game:GetService'GroupService':GetGroupInfoAsync(group).Roles
    local currentRole
    for index,info in next, ranks do
        if info.Rank == userRank then
            currentRole = index
        end
    end
    return ranks[currentRole+change].Rank
end
