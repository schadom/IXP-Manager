<?php

namespace Repositories;

use Doctrine\ORM\EntityRepository;

use Entities\Vlan as VlanEntity;

/**
 * VlanInterface
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VlanInterface extends EntityRepository
{

    /**
     * Utility function to provide an array of all VLAN interfaces on a given
     * VLAN for a given protocol.
     *
     * Returns an array of elements such as:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65500
     *         [gmaxprefixes] => 20        // from cust table (global)
     *         [peeringmacro] => ABC
     *         [peeringmacrov6] => ABC
     *         [vliid] => 159
     *         [enabled] => 1              // VLAN interface enabled for requested protocol?
     *         [address] => 192.0.2.123    // assigned address for requested protocol?
     *         [bgpmd5secret] => qwertyui  // MD5 for requested protocol
     *         [maxbgpprefix] => 20        // VLAN interface max prefixes
     *         [as112client] => 1          // if the member is an as112 client or not
     *         [rsclient] => 1             // if the member is a route server client or not
     *     ]
     *
     * @param \Entities\Vlan $vlan The VLAN
     * @param int $proto Either 4 or 6
     * @param bool $useResultCache If true, use Doctrine's result cache (ttl set to one hour)
     * @param int $pistatus The status of the physical interface
     * @return array As defined above.
     * @throws \IXP_Exception On bad / no protocol
     */
    public function getForProto( $vlan, $proto, $useResultCache = true, $pistatus = \Entities\PhysicalInterface::STATUS_CONNECTED )
    {
        if( !in_array( $proto, [ 4, 6 ] ) )
            throw new \IXP_Exception( 'Invalid protocol specified' );


        $qstr = "SELECT c.id AS cid, c.name AS cname, c.shortname AS cshortname, c.autsys AS autsys,
                       c.maxprefixes AS gmaxprefixes, c.peeringmacro as peeringmacro, c.peeringmacrov6 as peeringmacrov6,
                       vli.id AS vliid, vli.ipv{$proto}enabled AS enabled, addr.address AS address,
                       vli.ipv{$proto}bgpmd5secret AS bgpmd5secret, vli.maxbgpprefix AS maxbgpprefix,
                       vli.as112client AS as112client, vli.rsclient AS rsclient, vli.irrdbfilter AS irrdbfilter,
                       l.name AS location_name, l.shortname AS location_shortname, l.tag AS location_tag
                    FROM Entities\\VlanInterface vli
                        JOIN vli.VirtualInterface vi
                        JOIN vli.IPv{$proto}Address addr
                        JOIN vi.Customer c
                        JOIN vi.PhysicalInterfaces pi
                        JOIN pi.SwitchPort sp
                        JOIN sp.Switcher s
                        JOIN s.Cabinet cab
                        JOIN cab.Location l
                        JOIN vli.Vlan v
                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND pi.status = :pistatus";

        $qstr .= " ORDER BY c.autsys ASC, vli.id ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );
        $q->setParameter( 'pistatus', $pistatus );
        $q->useResultCache( $useResultCache, 3600 );
        return $q->getArrayResult();
    }


    /**
     * Utility function to provide an array of all VLAN interfaces on a given IXP.
     *
     * Returns an array of elements such as:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65500
     *         [vliid] => 159
     *
     *         [ipv4enabled]                   // VLAN interface enabled
     *         [ipv4canping]                   // Can ping for moniroting
     *         [ipv4hostname]                  // hostname
     *         [ipv4monitorrcbgp]              // Can monitor RC BGP session
     *         [ipv4address] => 192.0.2.123    // assigned address
     *         [ipv4bgpmd5secret] => qwertyui  // MD5
     *
     *         [ipv6enabled]                   // VLAN interface enabled
     *         [ipv6canping]                   // Can ping for moniroting
     *         [ipv6hostname]                  // hostname
     *         [ipv6monitorrcbgp]              // Can monitor RC BGP session
     *         [ipv6address] => 192.0.2.123    // assigned address
     *         [ipv6bgpmd5secret] => qwertyui  // MD5
     *
     *         [maxbgpprefix] => 20        // VLAN interface max prefixes
     *         [as112client] => 1          // if the member is an as112 client or not
     *         [rsclient] => 1             // if the member is a route server client or not
     *     ]
     *
     * @param \Entities\Vlan $vlan The VLAN
     * @param int $proto Either 4 or 6
     * @param bool $useResultCache If true, use Doctrine's result cache (ttl set to one hour)
     * @return array As defined above.
     * @throws \IXP_Exception On bad / no protocol
     */
    public function getForIXP( $ixp, $useResultCache = true )
    {
        $qstr = "SELECT c.id AS cid, c.name AS cname, c.shortname AS cshortname, c.autsys AS autsys,

                    vli.id AS vliid,

                    vli.ipv4enabled      AS ipv4enabled,
                    vli.ipv4hostname     AS ipv4hostname,
                    vli.ipv4canping      AS ipv4canping,
                    vli.ipv4monitorrcbgp AS ipv4monitorrcbgp,
                    vli.ipv4bgpmd5secret AS ipv4bgpmd5secret,
                    v4addr.address       AS ipv4address,

                    vli.ipv6enabled      AS ipv6enabled,
                    vli.ipv6hostname     AS ipv6hostname,
                    vli.ipv6canping      AS ipv6canping,
                    vli.ipv6monitorrcbgp AS ipv6monitorrcbgp,
                    vli.ipv6bgpmd5secret AS ipv6bgpmd5secret,
                    v6addr.address       AS ipv6address,

                    vli.maxbgpprefix AS maxbgpprefix,
                    vli.as112client AS as112client,
                    vli.rsclient AS rsclient,

                    s.name AS switchname,
                    sp.name AS switchport,

                    v.number AS vlannumber,

                    ixp.shortname AS ixpname

        FROM Entities\\VlanInterface vli
            JOIN vli.VirtualInterface vi
            JOIN vli.IPv4Address v4addr
            JOIN vli.IPv6Address v6addr
            JOIN vi.Customer c
            JOIN vi.PhysicalInterfaces pi
            JOIN pi.SwitchPort sp
            JOIN sp.Switcher s
            JOIN vli.Vlan v
            JOIN v.Infrastructure inf
            JOIN inf.IXP ixp

        WHERE
            ixp = :ixp
            AND " . Customer::DQL_CUST_ACTIVE     . "
            AND " . Customer::DQL_CUST_CURRENT    . "
            AND " . Customer::DQL_CUST_TRAFFICING . "
            AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED;

        $qstr .= " ORDER BY c.shortname ASC, vli.id ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );

        $q->setParameter( 'ixp', $ixp );
        $q->useResultCache( $useResultCache, 3600 );
        return $q->getArrayResult();
    }

    /**
     * Utility function to provide an array of all VLAN interfaces on a given
     * VLAN (optionally with active VLAN Interfaces for a given protocol).
     *
     * Returns an array of:
     *
     *     * Customer ID (cid)
     *     * Customer Name (cname)
     *     * Customer Shortname (cshortname)
     *     * VirtualInterface ID (id)
     *     * Physical Interface ID (pid)
     *     * VLAN Interface ID (vlanid)
     *     * SwithPort ID (spid)
     *     * Switch ID (swid)
     *
     * @param \Entities\Infrastructure $infra The infrastructure to gather VirtualInterfaces for
     * @param int $proto Either 4 or 6 to limit the results to interface with IPv4 / IPv6
     * @param bool $externalOnly If true (default) then only external (non-internal) interfaces will be returned
     * @param bool $useResultCache If true, use Doctrine's result cache to prevent needless database overhead
     * @return array As defined above.
     * @throws \IXP_Exception
     */
    public function getForVlan( $vlan, $proto = false, $externalOnly = true, $useResultCache = true )
    {
        $qstr = "SELECT c.id AS cid, c.name AS cname, c.shortname AS cshortname,
                       vi.id AS id, pi.id AS pid, vli.id AS vlanid, sp.id AS spid, sw.id as swid
                    FROM Entities\\VlanInterface vli
                        JOIN vli.Vlan v
                        JOIN vli.VirtualInterface vi
                        JOIN vi.Customer c
                        JOIN vi.PhysicalInterfaces pi
                        JOIN pi.SwitchPort sp
                        JOIN sp.Switcher sw
                        JOIN sw.Infrastructure i
                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED;

        if( $proto )
        {
            if( !in_array( $proto, [ 4, 6 ] ) )
                throw new \IXP_Exception( 'Invalid protocol specified' );

            $qstr .= "AND vli.ipv{$proto}enabled = 1 ";
        }

        if( $externalOnly )
            $qstr .= "AND " . Customer::DQL_CUST_EXTERNAL;

        $qstr .= " ORDER BY c.name ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );
        $q->useResultCache( $useResultCache, 3600 );
        return $q->getArrayResult();
    }

    /**
     * Utility function to provide an array of VLAN interface objects on a given VLAN.
     *
     * @param \Entities\Vlan $vlan The VLAN to gather VlanInterfaces for
     * @param bool $useResultCache If true, use Doctrine's result cache.
     * @return \Entities\VlanInterface[] Indexed by VlanInterface ID
     */
    public function getObjectsForVlan( $vlan, $useResultCache = true )
    {
        $qstr = "SELECT vli
                    FROM Entities\\VlanInterface vli
                        JOIN vli.Vlan v
                        JOIN vli.VirtualInterface vi
                        JOIN vi.PhysicalInterfaces pi
                        JOIN vi.Customer c

                    WHERE
                        v = :vlan
                        AND " . Customer::DQL_CUST_ACTIVE     . "
                        AND " . Customer::DQL_CUST_CURRENT    . "
                        AND " . Customer::DQL_CUST_TRAFFICING . "
                        AND " . Customer::DQL_CUST_EXTERNAL   . "
                        AND pi.status = " . \Entities\PhysicalInterface::STATUS_CONNECTED . "

                    ORDER BY c.name ASC";

        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'vlan', $vlan );
        $q->useResultCache( $useResultCache, 3600 );

        $vlis = [];
        foreach( $q->getResult() as $vli )
            $vlis[ $vli->getId() ] = $vli;

        return $vlis;
    }


    /**
     * Utility function to provide an array of all VLAN interface objects for a given
     * customer at an optionally given IXP.
     *
     * @param \Entities\Customer $customer The customer
     * @param \Entities\IXP      $ixp      The optional IXP
     * @param bool $useResultCache If true, use Doctrine's result cache
     * @return \Entities\VlanInterface[] Index by the VlanInterface ID
     */
    public function getForCustomer( $customer, $ixp = false, $useResultCache = true )
    {
        $qstr = "SELECT vli
                    FROM Entities\\VlanInterface vli
                        JOIN vli.VirtualInterface vi
                        JOIN vi.Customer c
                        JOIN vli.Vlan v";

        if( $ixp )
        {
            $qstr .= " JOIN vi.PhysicalInterfaces pi
                        JOIN pi.SwitchPort sp
                        JOIN sp.Switcher sw
                        JOIN sw.Infrastructure i
                        JOIN i.IXP ixp";
        }

        $qstr .= " WHERE c = :customer";

        if( $ixp )
        {
            $qstr .= " AND ixp = :ixp
                        ORDER BY ixp.id, v.number";
        }
        else
            $qstr .= " ORDER BY v.number";


        $q = $this->getEntityManager()->createQuery( $qstr );
        $q->setParameter( 'customer', $customer );

        if( $ixp )
            $q->setParameter( 'ixp', $ixp );

        $q->useResultCache( $useResultCache, 3600 );

        $vlis = [];

        foreach( $q->getResult() as $vli )
            $vlis[ $vli->getId() ] = $vli;

        return $vlis;
    }


    /**
     * Utility function to get and return active VLAN interfaces on the requested protocol
     * suitable for route collector / server configuration.
     *
     * Sample return:
     *
     *     [
     *         [cid] => 999
     *         [cname] => Customer Name
     *         [cshortname] => shortname
     *         [autsys] => 65000
     *         [peeringmacro] => QWE              // or AS65500 if not defined
     *         [vliid] => 159
     *         [fvliid] => 00159                  // formatted %05d
     *         [address] => 192.0.2.123
     *         [bgpmd5secret] => qwertyui         // or false
     *         [as112client] => 1                 // if the member is an as112 client or not
     *         [rsclient] => 1                    // if the member is a route server client or not
     *         [maxprefixes] => 20
     *         [irrdbfilter] => 0/1               // if IRRDB filtering should be applied
     *         [location_name] => Interxion DUB1
     *         [location_shortname] => IX-DUB1
     *         [location_tag] => ix1
     *     ]
     *
     * @param Vlan $vlan
     * @return array As defined above
     */
    public function sanitiseVlanInterfaces( VlanEntity $vlan, int $protocol = 4, string $target = 'RS', bool $quarantine = false ): array {

        $ints = $this->getForProto( $vlan, $protocol, false,
            $quarantine  ? \Entities\PhysicalInterface::STATUS_QUARANTINE : \Entities\PhysicalInterface::STATUS_CONNECTED
        );

        $newints = [];

        foreach( $ints as $int )
        {
            if( !$int['enabled'] ) {
                continue;
            }

            // don't need this anymore:
            unset( $int['enabled'] );


            if( $target == 'RS' && !$int['rsclient'] ) {
                continue;
            }

            // Due the the way we format the SQL query to join with physical
            // interfaces (of which there may be multiple per VLAN interface),
            // we need to weed out duplicates
            if( isset( $newints[ $int['address'] ] ) ) {
                continue;
            }

            $int['fvliid'] = sprintf( '%04d', $int['vliid'] );

            if( $int['maxbgpprefix'] && $int['maxbgpprefix'] > $int['gmaxprefixes'] ) {
                $int['maxprefixes'] = $int['maxbgpprefix'];
            } else {
                $int['maxprefixes'] = $int['gmaxprefixes'];
            }

            if( !$int['maxprefixes'] ) {
                $int['maxprefixes'] = 250;
            }

            unset( $int['gmaxprefixes'] );
            unset( $int['maxbgpprefix'] );

            if( $protocol == 6 && $int['peeringmacrov6'] ) {
                $int['peeringmacro'] = $int['peeringmacrov6'];
            }

            if( !$int['peeringmacro'] ) {
                $int['peeringmacro'] = 'AS' . $int['autsys'];
            }

            unset( $int['peeringmacrov6'] );

            if( !$int['bgpmd5secret'] ) {
                $int['bgpmd5secret'] = false;
            }

            if( $int['irrdbfilter'] ) {
                $int['irrdbfilter_prefixes'] = d2r( 'IrrdbPrefix' )->getForCustomerAndProtocol( $int[ 'cid' ], $protocol, true );
                $int['irrdbfilter_asns'    ] = d2r( 'IrrdbAsn'    )->getForCustomerAndProtocol( $int[ 'cid' ], $protocol, true );
            }

            $newints[ $int['address'] ] = $int;
        }

        return $newints;
    }


}