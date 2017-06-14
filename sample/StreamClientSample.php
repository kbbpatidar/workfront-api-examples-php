<?php
/*
 * Copyright (c) 2011 AtTask, Inc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

require('../src/StreamClient.php');

function write() {
	$max=func_num_args();
	for ($i=0; $i<$max; $i++) {
		echo func_get_arg($i);
	}
}

function writeln() {
	call_user_func_array('write', func_get_args());
	echo PHP_EOL;
}

try {
	// Create an instance of the Stream API client
	$client = new StreamClient('http://localhost:8080');
	
	// Login
	write('Logging in...');
	$session = $client->login('admin', 'user');
	writeln('done');
	writeln();

	// Get user
	write('Retrieving user...');
	$user = $client->get('user', $session->userID, array('ID', 'homeGroupID', 'emailAddr'));
	writeln('done');
	writeln();

	// Search projects
	write('Searching projects...');
	$results = $client->search('project', array('groupID' => $user->homeGroupID), array('ID', 'name'));
	writeln('done');

	for ($i=0; $i<min(10, sizeof($results)); $i++) {
		writeln(' - ', $results[$i]->name);
	}
	writeln();

	// Count projects
	write('Counting projects...');
	$count = $client->count('project', array('groupID' => $user->homeGroupID));
	writeln('done');

	writeln('Group has ', $count, ' projects');
	writeln();

	// Report projects
	write('Getting project hours report...');
	$results = $client->report('project', array('groupID' => $user->homeGroupID, 'name_1_GroupBy' => true, 'hours_AggFunc' => 'sum'));
	writeln('done');

	$i=0;
	foreach ($results as $result) {
		writeln(' - ', $result->name, ' (', (empty($result->sum_hours_hours) ? 0 : $result->sum_hours_hours), ' hours)');
		if (++$i == 10) break;
	}
	writeln();

	// Named query
	write('Getting my work...');
	$results = $client->namedquery('work', 'myWork');
	writeln('done');

	for ($i=0; $i<min(10, sizeof($results)); $i++) {
		writeln(' - ', $results[$i]->name);
	}
	writeln();

	// Batch operation
	write('Performing batch operation...');
	$client->batchStart();
	$client->namedquery('work', 'myWork');
	$client->count('project', array('groupID' => $user->homeGroupID));
	$client->batchEnd();
	writeln('done');
	writeln();

	// Create project
	write('Creating project...');
	$proj = $client->post('project', array('name' => 'My Project', 'groupID' => $user->homeGroupID));
	writeln('done');
	writeln();

	// Get project
	write('Retrieving project...');
	$proj = $client->get('project', $proj->ID);
	writeln('done');
	writeln();

	// Edit project
	write('Editing project...');
	$proj = $client->put('project', $proj->ID, array('name' => 'Your Project'));
	writeln('done');
	writeln();

	// Copy project
	write('Copying project...');
	$copy = $client->copy('project', $proj->ID, array('name' => 'Your Project (Copy)'));
	writeln('done');
	writeln();

	// Delete project
	write('Deleting project...');
	$client->batchStart(true);
	$client->delete('project', $proj->ID);
	$client->delete('project', $copy->ID);
	$client->batchEnd();
	writeln('done');
	writeln();

	// Logout
	write('Logging out...');
	$client->logout();
	writeln('done');
}
catch (StreamClientException $e) {
	write('Error: ', $e->getMessage());
}

unset($client);

?>