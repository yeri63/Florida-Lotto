<?php
    # Program: lottogen.php
    #    Date: 07-May-2020
    #  Author: gerry.walterscheid@gmail.com
    # Descrip: Generate Florida Lotto picks for next game.

    session_start();
    header("Content-type: text/xml");

    $urlPath = "http://192.168.3.250/cisco/lotto.php";

    # Recall prior variable state
    $id = $_SESSION['id'];                  # App name.

    # Prevent data corruption from prior application.
    if ($id == "lottogen") {
        $lastCmd = $_SESSION['cmd'];        # User command.
        $mode = $_SESSION['mode'] + 0;      # Last mode.
        $game = $_SESSION['game'];          # Game numbers.
        $count = $_SESSION['count']; }      # Numbers drawn.

    else {
        $lastCmd = 0;
        $mode = 0; 
        $game = [];
        $count = 0; }

    # Get SoftKey selection.
    $cmd = $_GET['key'] + 0;

    # Process user request based on the key pressed.	
    switch($cmd) {

    case 1 : # rules
        if ($lastCmd < 2)
            $mode = ++$mode % 2;
        else
            $mode = 1;
        break;

    case 2 : # pick
        $mode = 2;

        # Do nothing when 1st selected.
        if ($lastCmd != 2)
            break;

        # Reset if the game was complete.
        if ($count++ > 5) {
            $game = [];
            $count = 0; }
        else {
            # Make sure there are no duplicates.
            $pick = 0;
            $game[$pick] = 1;

			# Generate random pick.
			do {
				$pick = random_int(1,53);
			} while (isset($game[$pick]));

			# Mark the number as picked.
            $game[$pick] = 1; }
        break;

    case 3 : # wins
        $mode = 3;
        break;
    }

    switch($mode) {
    case 0 : # intro
        echo "<CiscoIPPhoneText>\n";
        echo "  <Title>Florida Lotto Generator</Title>\n";

        echo "  <Text>";
        echo "Florida's original jackpot game. Jackpots start ";
        echo "at $2 million and roll until there is a winner.";
        echo "</Text>\n";

        $mesg = "";
        break;

    case 1 : # rules
        echo "<CiscoIPPhoneText>\n";
        echo "  <Title>Florida Lotto - Game Rules</Title>\n";

        echo "  <Text>";
        echo "1. Select six numbers from 1 through 53, or mark ";
        echo "the QP (Quick Pick) box and let the terminal ";
        echo "randomly pick some or all of your numbers.\n\n";

        echo "2. To play the same numbers for more than one ";
        echo "draw, select the number of draws you want to ";
        echo "play in the Advance Play section of your playslip.\n\n";

        echo "3. To multiply your winnings on non-jackpot ";
        echo "prizes, select the XTRA box. XTRA costs $1 ";
        echo "more per play. The XTRA box applies to all panels.\n\n";

        echo "\t\tMatch \t\tPayout\n";
        echo "\t\t6 of 6\t\tJackpot\n";
        echo "\t\t5 of 6\t\t$5,000\n";
        echo "\t\t4 of 6\t\t   $70\n";
        echo "\t\t3 of 6\t\t    $5\n\n";

        echo "The XTRA number, from 2 to 5, will be drawn at ";
        echo "random just before the FLORIDA LOTTO drawing to ";
        echo "determine how much the non-jackpot cash prizes ";
        echo "will be increased for that draw.\n";
        echo "</Text>\n";

        $mesg = "Use arrows to view all text.";
        break;

    case 2 : # pick
        echo "<CiscoIPPhoneText>\n";
        echo "  <Title>Florida Lotto - Pick Numbers</Title>\n";

        echo "  <Text>\n\n\t";

        # Print out list of picks for this game.
        for ($i = 1; $i < 54; $i++) 
            if (!empty($game[$i]))
                printf("\t%02d",$i);

        echo "</Text>\n";

        if ($count > 5) 
            $mesg = "Play these numbers!"; 
        else
            $mesg = "Get your next number."; 

        break;

    case 3 : # wins
        echo "<CiscoIPPhoneText>\n";
        echo "  <Title>Florida Lotto - Winning Tickets</Title>\n";
    	echo "  <Text>";

        # Call script to get historical Lotto game data.
        exec('./lotto-history.sh', $history);

        $mesg = "Use arrows to view all text.";

        # Parse data from Lotto and display on screen.
        for ($i = 0; $i < 26; $i++) {
            $data = explode(' ', $history[$i]);
             for ($j = 0; $j < 6; $j++)
                 echo "$data[$j]\t";

        echo "$data[6]\n"; }

        echo "</Text>\n";
        break;
    }

    echo "  <SoftKeyItem>\n";
    echo "    <Name>Rules</Name>\n";
    echo "    <URL>$urlPath?key=1</URL>\n";
    echo "    <Position>1</Position>\n";
    echo "  </SoftKeyItem>\n";

    echo "  <SoftKeyItem>\n";
    echo "    <Name>Pick</Name>\n";
    echo "    <URL>$urlPath?key=2</URL>\n";
    echo "    <Position>2</Position>\n";
    echo "  </SoftKeyItem>\n";

    echo "  <SoftKeyItem>\n";
    echo "    <Name>Wins</Name>\n";
    echo "    <URL>$urlPath?key=3</URL>\n";
    echo "    <Position>3</Position>\n";
    echo "  </SoftKeyItem>\n";

    echo "  <SoftKeyItem>\n";
    echo "    <Name>Exit</Name>\n";
    echo "    <URL>SoftKey:Exit</URL>\n";
    echo "    <Position>4</Position>\n";
    echo "  </SoftKeyItem>\n";

    echo "<Prompt>$mesg</Prompt>\n";
    echo "</CiscoIPPhoneText>";

    # Save current variable state.
    $_SESSION['id'] = "lottogen";
    $_SESSION['cmd'] = $cmd;
    $_SESSION['mode'] = $mode;
    $_SESSION['game'] = $game;
    $_SESSION['count'] = $count;
?>
