<?php

namespace AlibabaCloud\Drds\V20190123;

use AlibabaCloud\Client\Resolver\ApiResolver;

/**
 * @method AsyncTaskCancel asyncTaskCancel(array $options = [])
 * @method ChangeAccountPassword changeAccountPassword(array $options = [])
 * @method CheckConnectivity checkConnectivity(array $options = [])
 * @method CheckDrdsDbName checkDrdsDbName(array $options = [])
 * @method CheckExpandStatus checkExpandStatus(array $options = [])
 * @method CheckNewTableNameValid checkNewTableNameValid(array $options = [])
 * @method CheckSqlAuditEnableStatus checkSqlAuditEnableStatus(array $options = [])
 * @method CreateCustomDataExportPreCheckTask createCustomDataExportPreCheckTask(array $options = [])
 * @method CreateCustomDataExportTask createCustomDataExportTask(array $options = [])
 * @method CreateCustomDataImportPreCheckTask createCustomDataImportPreCheckTask(array $options = [])
 * @method CreateCustomDataImportTask createCustomDataImportTask(array $options = [])
 * @method CreateDrdsDB createDrdsDB(array $options = [])
 * @method CreateDrdsInstance createDrdsInstance(array $options = [])
 * @method CreateEvaluateDataImportPreCheckTask createEvaluateDataImportPreCheckTask(array $options = [])
 * @method CreateEvaluateDataImportTask createEvaluateDataImportTask(array $options = [])
 * @method CreateEvaluatePreCheckTask createEvaluatePreCheckTask(array $options = [])
 * @method CreateEvaluateTask createEvaluateTask(array $options = [])
 * @method CreateInstanceAccount createInstanceAccount(array $options = [])
 * @method CreateInstanceInternetAddress createInstanceInternetAddress(array $options = [])
 * @method CreateMyCatCustomImport createMyCatCustomImport(array $options = [])
 * @method CreateMyCatEvaluate createMyCatEvaluate(array $options = [])
 * @method CreateOrderForRds createOrderForRds(array $options = [])
 * @method CreateShardTask createShardTask(array $options = [])
 * @method DatalinkReplicationPrecheck datalinkReplicationPrecheck(array $options = [])
 * @method DeleteShardTasks deleteShardTasks(array $options = [])
 * @method DescribeBackMenu describeBackMenu(array $options = [])
 * @method DescribeBackupDbs describeBackupDbs(array $options = [])
 * @method DescribeBackupLocal describeBackupLocal(array $options = [])
 * @method DescribeBackupPolicy describeBackupPolicy(array $options = [])
 * @method DescribeBackupSets describeBackupSets(array $options = [])
 * @method DescribeBackupTimes describeBackupTimes(array $options = [])
 * @method DescribeBatchEvaluateTaskReport describeBatchEvaluateTaskReport(array $options = [])
 * @method DescribeBroadcastTables describeBroadcastTables(array $options = [])
 * @method DescribeCustomDataExportSrcDstTables describeCustomDataExportSrcDstTables(array $options = [])
 * @method DescribeCustomDataImportSrcDstTables describeCustomDataImportSrcDstTables(array $options = [])
 * @method DescribeDataExportPreCheckResult describeDataExportPreCheckResult(array $options = [])
 * @method DescribeDataExportTaskReport describeDataExportTaskReport(array $options = [])
 * @method DescribeDataExportTasks describeDataExportTasks(array $options = [])
 * @method DescribeDataImportPreCheckResult describeDataImportPreCheckResult(array $options = [])
 * @method DescribeDataImportTaskReport describeDataImportTaskReport(array $options = [])
 * @method DescribeDataImportTasks describeDataImportTasks(array $options = [])
 * @method DescribeDbInstanceDbs describeDbInstanceDbs(array $options = [])
 * @method DescribeDbInstances describeDbInstances(array $options = [])
 * @method DescribeDrdsComponents describeDrdsComponents(array $options = [])
 * @method DescribeDrdsDB describeDrdsDB(array $options = [])
 * @method DescribeDrdsDBCluster describeDrdsDBCluster(array $options = [])
 * @method DescribeDrdsDbInstance describeDrdsDbInstance(array $options = [])
 * @method DescribeDrdsDbInstances describeDrdsDbInstances(array $options = [])
 * @method DescribeDrdsDBIpWhiteList describeDrdsDBIpWhiteList(array $options = [])
 * @method DescribeDrdsDbRdsNameList describeDrdsDbRdsNameList(array $options = [])
 * @method DescribeDrdsDBs describeDrdsDBs(array $options = [])
 * @method DescribeDrdsDbTasks describeDrdsDbTasks(array $options = [])
 * @method DescribeDrdsDbTestLink describeDrdsDbTestLink(array $options = [])
 * @method DescribeDrdsInstance describeDrdsInstance(array $options = [])
 * @method DescribeDrdsInstanceDbMonitor describeDrdsInstanceDbMonitor(array $options = [])
 * @method DescribeDrdsInstanceLevelTasks describeDrdsInstanceLevelTasks(array $options = [])
 * @method DescribeDrdsInstanceMonitor describeDrdsInstanceMonitor(array $options = [])
 * @method DescribeDrdsInstances describeDrdsInstances(array $options = [])
 * @method DescribeDrdsInstanceVersion describeDrdsInstanceVersion(array $options = [])
 * @method DescribeDrdsParams describeDrdsParams(array $options = [])
 * @method DescribeDrdsRdsInstances describeDrdsRdsInstances(array $options = [])
 * @method DescribeDrdsShardingDbs describeDrdsShardingDbs(array $options = [])
 * @method DescribeDrdsSlowSqls describeDrdsSlowSqls(array $options = [])
 * @method DescribeDrdsSqlAuditStatus describeDrdsSqlAuditStatus(array $options = [])
 * @method DescribeDrdsTasks describeDrdsTasks(array $options = [])
 * @method DescribeEvaluateDataImportDbTopologys describeEvaluateDataImportDbTopologys(array $options = [])
 * @method DescribeEvaluatePreCheckResult describeEvaluatePreCheckResult(array $options = [])
 * @method DescribeEvaluateTaskReport describeEvaluateTaskReport(array $options = [])
 * @method DescribeEvaluateTasks describeEvaluateTasks(array $options = [])
 * @method DescribeExpandLogicTableInfoList describeExpandLogicTableInfoList(array $options = [])
 * @method DescribeFirstDrdsInstanceByOrderId describeFirstDrdsInstanceByOrderId(array $options = [])
 * @method DescribeGlobalBroadcastType describeGlobalBroadcastType(array $options = [])
 * @method DescribeHiStoreInstanceInfo describeHiStoreInstanceInfo(array $options = [])
 * @method DescribeHotDbList describeHotDbList(array $options = [])
 * @method DescribeInstanceAccounts describeInstanceAccounts(array $options = [])
 * @method DescribeInstanceMenuSwitch describeInstanceMenuSwitch(array $options = [])
 * @method DescribeInstanceSwitchAzone describeInstanceSwitchAzone(array $options = [])
 * @method DescribeInstanceSwitchNetwork describeInstanceSwitchNetwork(array $options = [])
 * @method DescribeInstDbLogInfo describeInstDbLogInfo(array $options = [])
 * @method DescribeInstDbSlsInfo describeInstDbSlsInfo(array $options = [])
 * @method DescribeMyCatDbList describeMyCatDbList(array $options = [])
 * @method DescribeMyCatReportInfo describeMyCatReportInfo(array $options = [])
 * @method DescribePreCheckResult describePreCheckResult(array $options = [])
 * @method DescribeRdsCommodity describeRdsCommodity(array $options = [])
 * @method DescribeRdsDbInfos describeRdsDbInfos(array $options = [])
 * @method DescribeRdsDrdsDB describeRdsDrdsDB(array $options = [])
 * @method DescribeRdsInstInfos describeRdsInstInfos(array $options = [])
 * @method DescribeRDSPerformance describeRDSPerformance(array $options = [])
 * @method DescribeRdsPerformanceSummary describeRdsPerformanceSummary(array $options = [])
 * @method DescribeRdsReadOnly describeRdsReadOnly(array $options = [])
 * @method DescribeRdsSuperAccountInstances describeRdsSuperAccountInstances(array $options = [])
 * @method DescribeRecycleBinStatus describeRecycleBinStatus(array $options = [])
 * @method DescribeRecycleBinTables describeRecycleBinTables(array $options = [])
 * @method DescribeRestoreOrder describeRestoreOrder(array $options = [])
 * @method DescribeShardTaskInfo describeShardTaskInfo(array $options = [])
 * @method DescribeShardTaskList describeShardTaskList(array $options = [])
 * @method DescribeSqlFlashbakTask describeSqlFlashbakTask(array $options = [])
 * @method DescribeTable describeTable(array $options = [])
 * @method DescribeTableListByType describeTableListByType(array $options = [])
 * @method DescribeTables describeTables(array $options = [])
 * @method DescribeTableShardingInfo describeTableShardingInfo(array $options = [])
 * @method DescribeUnCompatibilitySql describeUnCompatibilitySql(array $options = [])
 * @method DisableSqlAudit disableSqlAudit(array $options = [])
 * @method EnableInstanceIpv6Address enableInstanceIpv6Address(array $options = [])
 * @method EnableSqlAudit enableSqlAudit(array $options = [])
 * @method EnableSqlFlashbackMatchSwitch enableSqlFlashbackMatchSwitch(array $options = [])
 * @method FlashbackRecycleBinTable flashbackRecycleBinTable(array $options = [])
 * @method GetBatchEvaluateTaskReport getBatchEvaluateTaskReport(array $options = [])
 * @method ListTagResources listTagResources(array $options = [])
 * @method ListUserReports listUserReports(array $options = [])
 * @method ListVersions listVersions(array $options = [])
 * @method ManagePrivateRds managePrivateRds(array $options = [])
 * @method ModifyAccountDescription modifyAccountDescription(array $options = [])
 * @method ModifyAccountPrivilege modifyAccountPrivilege(array $options = [])
 * @method ModifyDrdsInstanceDescription modifyDrdsInstanceDescription(array $options = [])
 * @method ModifyDrdsIpWhiteList modifyDrdsIpWhiteList(array $options = [])
 * @method ModifyPolarDbReadWeight modifyPolarDbReadWeight(array $options = [])
 * @method ModifyRdsReadWeight modifyRdsReadWeight(array $options = [])
 * @method MyCatConnectTest myCatConnectTest(array $options = [])
 * @method MyCatCustomImportPreCheck myCatCustomImportPreCheck(array $options = [])
 * @method PreCheckModifyTableShardingKeyParam preCheckModifyTableShardingKeyParam(array $options = [])
 * @method PrecheckMyCatEvaluate precheckMyCatEvaluate(array $options = [])
 * @method PutStartBackup putStartBackup(array $options = [])
 * @method RefreshDrdsAtomUrl refreshDrdsAtomUrl(array $options = [])
 * @method ReleaseHiStoreInstance releaseHiStoreInstance(array $options = [])
 * @method ReleaseInstanceInternetAddress releaseInstanceInternetAddress(array $options = [])
 * @method RemoveBackupsSet removeBackupsSet(array $options = [])
 * @method RemoveDataExportTask removeDataExportTask(array $options = [])
 * @method RemoveDataImportTask removeDataImportTask(array $options = [])
 * @method RemoveDrdsDb removeDrdsDb(array $options = [])
 * @method RemoveDrdsDbFailedRecord removeDrdsDbFailedRecord(array $options = [])
 * @method RemoveDrdsInstance removeDrdsInstance(array $options = [])
 * @method RemoveDrdsMysql removeDrdsMysql(array $options = [])
 * @method RemoveEvaluateTask removeEvaluateTask(array $options = [])
 * @method RemoveInstanceAccount removeInstanceAccount(array $options = [])
 * @method RemoveRecycleBinTable removeRecycleBinTable(array $options = [])
 * @method ResetDrdsToRdsConnections resetDrdsToRdsConnections(array $options = [])
 * @method RestartDrdsInstance restartDrdsInstance(array $options = [])
 * @method RollbackHiStoreInstance rollbackHiStoreInstance(array $options = [])
 * @method RollbackInstanceVersion rollbackInstanceVersion(array $options = [])
 * @method SetBackupLocal setBackupLocal(array $options = [])
 * @method SetBackupPolicy setBackupPolicy(array $options = [])
 * @method SetupBroadcastTables setupBroadcastTables(array $options = [])
 * @method SetupDrdsParams setupDrdsParams(array $options = [])
 * @method SetupRecycleBinStatus setupRecycleBinStatus(array $options = [])
 * @method SetupTable setupTable(array $options = [])
 * @method SqlCompatibilityCancel sqlCompatibilityCancel(array $options = [])
 * @method SqlCompatibilityStart sqlCompatibilityStart(array $options = [])
 * @method StartDataImportTask startDataImportTask(array $options = [])
 * @method StartEvaluateTask startEvaluateTask(array $options = [])
 * @method StartRestore startRestore(array $options = [])
 * @method StopDataExportTask stopDataExportTask(array $options = [])
 * @method StopDataImportTask stopDataImportTask(array $options = [])
 * @method StopEvaluateTask stopEvaluateTask(array $options = [])
 * @method SubmitCleanTableShardingKeyModify submitCleanTableShardingKeyModify(array $options = [])
 * @method SubmitCleanTask submitCleanTask(array $options = [])
 * @method SubmitHotExpandPreCheckTask submitHotExpandPreCheckTask(array $options = [])
 * @method SubmitHotExpandTask submitHotExpandTask(array $options = [])
 * @method SubmitRollbackShardingKeyModify submitRollbackShardingKeyModify(array $options = [])
 * @method SubmitRollbackTask submitRollbackTask(array $options = [])
 * @method SubmitSmoothExpandPreCheck submitSmoothExpandPreCheck(array $options = [])
 * @method SubmitSmoothExpandPreCheckTask submitSmoothExpandPreCheckTask(array $options = [])
 * @method SubmitSmoothExpandTask submitSmoothExpandTask(array $options = [])
 * @method SubmitSqlFlashbackTask submitSqlFlashbackTask(array $options = [])
 * @method SubmitSwitchTableShardingKeyModify submitSwitchTableShardingKeyModify(array $options = [])
 * @method SubmitSwitchTask submitSwitchTask(array $options = [])
 * @method SubmitTableShardingKeyModify submitTableShardingKeyModify(array $options = [])
 * @method SwitchGlobalBroadcastType switchGlobalBroadcastType(array $options = [])
 * @method TagResources tagResources(array $options = [])
 * @method UntagResources untagResources(array $options = [])
 * @method UpdateInstanceNetwork updateInstanceNetwork(array $options = [])
 * @method UpdateResourceGroupAttribute updateResourceGroupAttribute(array $options = [])
 * @method UpgradeHiStoreInstance upgradeHiStoreInstance(array $options = [])
 * @method UpgradeInstanceVersion upgradeInstanceVersion(array $options = [])
 * @method ValidateShardTask validateShardTask(array $options = [])
 */
class DrdsApiResolver extends ApiResolver
{
}

class Rpc extends \AlibabaCloud\Client\Resolver\Rpc
{
    /** @var string */
    public $product = 'Drds';

    /** @var string */
    public $version = '2019-01-23';

    /** @var string */
    public $method = 'POST';

    /** @var string */
    public $serviceCode = 'Drds';
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class AsyncTaskCancel extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPassword()
 * @method $this withPassword($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 */
class ChangeAccountPassword extends Rpc
{
}

/**
 * @method string getDbInfo()
 * @method $this withDbInfo($value)
 * @method string getDbType()
 * @method $this withDbType($value)
 */
class CheckConnectivity extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class CheckDrdsDbName extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class CheckExpandStatus extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 */
class CheckNewTableNameValid extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class CheckSqlAuditEnableStatus extends Rpc
{
}

/**
 * @method string getExportParam()
 * @method $this withExportParam($value)
 */
class CreateCustomDataExportPreCheckTask extends Rpc
{
}

/**
 * @method string getExportParam()
 * @method $this withExportParam($value)
 */
class CreateCustomDataExportTask extends Rpc
{
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class CreateCustomDataImportPreCheckTask extends Rpc
{
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class CreateCustomDataImportTask extends Rpc
{
}

/**
 * @method string getEncode()
 * @method $this withEncode($value)
 * @method array getRdsInstance()
 * @method string getType()
 * @method $this withType($value)
 * @method string getPassword()
 * @method $this withPassword($value)
 * @method array getRdsSuperAccount()
 * @method string getAccountName()
 * @method $this withAccountName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbInstanceIsCreating()
 * @method $this withDbInstanceIsCreating($value)
 * @method array getInstDbName()
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class CreateDrdsDB extends Rpc
{

    /**
     * @param array $rdsInstance
     *
     * @return $this
     */
	public function withRdsInstance(array $rdsInstance)
	{
	    $this->data['RdsInstance'] = $rdsInstance;
		foreach ($rdsInstance as $i => $iValue) {
			$this->options['query']['RdsInstance.' . ($i + 1)] = $iValue;
		}

		return $this;
    }

    /**
     * @param array $rdsSuperAccount
     *
     * @return $this
     */
	public function withRdsSuperAccount(array $rdsSuperAccount)
	{
	    $this->data['RdsSuperAccount'] = $rdsSuperAccount;
		foreach ($rdsSuperAccount as $depth1 => $depth1Value) {
			if(isset($depth1Value['Password'])){
				$this->options['query']['RdsSuperAccount.' . ($depth1 + 1) . '.Password'] = $depth1Value['Password'];
			}
			if(isset($depth1Value['AccountName'])){
				$this->options['query']['RdsSuperAccount.' . ($depth1 + 1) . '.AccountName'] = $depth1Value['AccountName'];
			}
			if(isset($depth1Value['DbInstanceId'])){
				$this->options['query']['RdsSuperAccount.' . ($depth1 + 1) . '.DbInstanceId'] = $depth1Value['DbInstanceId'];
			}
		}

		return $this;
    }

    /**
     * @param array $instDbName
     *
     * @return $this
     */
	public function withInstDbName(array $instDbName)
	{
	    $this->data['InstDbName'] = $instDbName;
		foreach ($instDbName as $depth1 => $depth1Value) {
			foreach ($depth1Value['ShardDbName'] as $i => $iValue) {
				$this->options['query']['InstDbName.' . ($depth1 + 1) . '.ShardDbName.' . ($i + 1)] = $iValue;
			}
			if(isset($depth1Value['DbInstanceId'])){
				$this->options['query']['InstDbName.' . ($depth1 + 1) . '.DbInstanceId'] = $depth1Value['DbInstanceId'];
			}
		}

		return $this;
    }
}

/**
 * @method string getIsAutoRenew()
 * @method $this withIsAutoRenew($value)
 * @method string getClientToken()
 * @method $this withClientToken($value)
 * @method string getDescription()
 * @method $this withDescription($value)
 * @method string getType()
 * @method $this withType($value)
 * @method string getDuration()
 * @method $this withDuration($value)
 * @method string getResourceGroupId()
 * @method $this withResourceGroupId($value)
 * @method string getIsHa()
 * @method string getMySQLVersion()
 * @method $this withMySQLVersion($value)
 * @method string getInstanceSeries()
 * @method $this withInstanceSeries($value)
 * @method string getMasterInstId()
 * @method $this withMasterInstId($value)
 * @method string getQuantity()
 * @method $this withQuantity($value)
 * @method string getSpecification()
 * @method $this withSpecification($value)
 * @method string getVswitchId()
 * @method $this withVswitchId($value)
 * @method string getVpcId()
 * @method $this withVpcId($value)
 * @method string getZoneId()
 * @method $this withZoneId($value)
 * @method string getPayType()
 * @method $this withPayType($value)
 * @method string getPricingCycle()
 * @method $this withPricingCycle($value)
 */
class CreateDrdsInstance extends Rpc
{

    /**
     * @param string $value
     *
     * @return $this
     */
    public function withIsHa($value)
    {
        $this->data['IsHa'] = $value;
        $this->options['query']['isHa'] = $value;

        return $this;
    }
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class CreateEvaluateDataImportPreCheckTask extends Rpc
{
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class CreateEvaluateDataImportTask extends Rpc
{
}

/**
 * @method string getAvgQpsGrowthScale()
 * @method $this withAvgQpsGrowthScale($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getDataGrowthScale()
 * @method $this withDataGrowthScale($value)
 * @method array getDbInfo()
 * @method string getEvaluateHours()
 * @method $this withEvaluateHours($value)
 */
class CreateEvaluatePreCheckTask extends Rpc
{

    /**
     * @param array $dbInfo
     *
     * @return $this
     */
	public function withDbInfo(array $dbInfo)
	{
	    $this->data['DbInfo'] = $dbInfo;
		foreach ($dbInfo as $depth1 => $depth1Value) {
			if(isset($depth1Value['InstId'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.InstId'] = $depth1Value['InstId'];
			}
			if(isset($depth1Value['DbPort'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbPort'] = $depth1Value['DbPort'];
			}
			if(isset($depth1Value['DbName'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['DbPassword'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbPassword'] = $depth1Value['DbPassword'];
			}
			if(isset($depth1Value['DbUser'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbUser'] = $depth1Value['DbUser'];
			}
		}

		return $this;
    }
}

/**
 * @method string getAvgQpsGrowthScale()
 * @method $this withAvgQpsGrowthScale($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getDataGrowthScale()
 * @method $this withDataGrowthScale($value)
 * @method array getDbInfo()
 * @method string getEvaluateHours()
 * @method $this withEvaluateHours($value)
 */
class CreateEvaluateTask extends Rpc
{

    /**
     * @param array $dbInfo
     *
     * @return $this
     */
	public function withDbInfo(array $dbInfo)
	{
	    $this->data['DbInfo'] = $dbInfo;
		foreach ($dbInfo as $depth1 => $depth1Value) {
			if(isset($depth1Value['InstId'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.InstId'] = $depth1Value['InstId'];
			}
			if(isset($depth1Value['DbPort'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbPort'] = $depth1Value['DbPort'];
			}
			if(isset($depth1Value['DbName'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['DbPassword'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbPassword'] = $depth1Value['DbPassword'];
			}
			if(isset($depth1Value['DbUser'])){
				$this->options['query']['DbInfo.' . ($depth1 + 1) . '.DbUser'] = $depth1Value['DbUser'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPassword()
 * @method $this withPassword($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 * @method array getDbPrivilege()
 */
class CreateInstanceAccount extends Rpc
{

    /**
     * @param array $dbPrivilege
     *
     * @return $this
     */
	public function withDbPrivilege(array $dbPrivilege)
	{
	    $this->data['DbPrivilege'] = $dbPrivilege;
		foreach ($dbPrivilege as $depth1 => $depth1Value) {
			if(isset($depth1Value['DbName'])){
				$this->options['query']['DbPrivilege.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['Privilege'])){
				$this->options['query']['DbPrivilege.' . ($depth1 + 1) . '.Privilege'] = $depth1Value['Privilege'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class CreateInstanceInternetAddress extends Rpc
{
}

/**
 * @method string getDstPort()
 * @method $this withDstPort($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getDstUser()
 * @method $this withDstUser($value)
 * @method string getDstDbNme()
 * @method $this withDstDbNme($value)
 * @method string getTableMap()
 * @method $this withTableMap($value)
 * @method string getImportDb()
 * @method $this withImportDb($value)
 * @method string getDstPwd()
 * @method $this withDstPwd($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 * @method string getDstDrdsInstanceId()
 * @method $this withDstDrdsInstanceId($value)
 */
class CreateMyCatCustomImport extends Rpc
{
}

/**
 * @method string getSequenceType()
 * @method $this withSequenceType($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getBusPeakQps()
 * @method $this withBusPeakQps($value)
 * @method string getQpsIncPercent()
 * @method $this withQpsIncPercent($value)
 * @method string getServerUrl()
 * @method $this withServerUrl($value)
 * @method string getCapacityIncPercent()
 * @method $this withCapacityIncPercent($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 * @method array getImportDbList()
 */
class CreateMyCatEvaluate extends Rpc
{

    /**
     * @param array $importDbList
     *
     * @return $this
     */
	public function withImportDbList(array $importDbList)
	{
	    $this->data['ImportDbList'] = $importDbList;
		foreach ($importDbList as $i => $iValue) {
			$this->options['query']['ImportDbList.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getParams()
 * @method $this withParams($value)
 */
class CreateOrderForRds extends Rpc
{
}

/**
 * @method string getTaskType()
 * @method $this withTaskType($value)
 * @method string getTargetTableName()
 * @method $this withTargetTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getSourceTableName()
 * @method $this withSourceTableName($value)
 */
class CreateShardTask extends Rpc
{
}

/**
 * @method string getSrcTableName()
 * @method $this withSrcTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDstTableName()
 * @method $this withDstTableName($value)
 */
class DatalinkReplicationPrecheck extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method array getTableName()
 */
class DeleteShardTasks extends Rpc
{

    /**
     * @param array $tableName
     *
     * @return $this
     */
	public function withTableName(array $tableName)
	{
	    $this->data['TableName'] = $tableName;
		foreach ($tableName as $depth1 => $depth1Value) {
			if(isset($depth1Value['SourceTableName'])){
				$this->options['query']['TableName.' . ($depth1 + 1) . '.SourceTableName'] = $depth1Value['SourceTableName'];
			}
			if(isset($depth1Value['TargetTableName'])){
				$this->options['query']['TableName.' . ($depth1 + 1) . '.TargetTableName'] = $depth1Value['TargetTableName'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackMenu extends Rpc
{
}

/**
 * @method string getPreferredRestoreTime()
 * @method $this withPreferredRestoreTime($value)
 * @method string getBackupId()
 * @method $this withBackupId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackupDbs extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackupLocal extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackupPolicy extends Rpc
{
}

/**
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackupSets extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeBackupTimes extends Rpc
{
}

/**
 * @method string getBatchEvaluateTaskId()
 * @method $this withBatchEvaluateTaskId($value)
 */
class DescribeBatchEvaluateTaskReport extends Rpc
{
}

/**
 * @method string getQuery()
 * @method $this withQuery($value)
 * @method string getCurrentPage()
 * @method $this withCurrentPage($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeBroadcastTables extends Rpc
{
}

/**
 * @method string getExportParam()
 * @method $this withExportParam($value)
 */
class DescribeCustomDataExportSrcDstTables extends Rpc
{
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class DescribeCustomDataImportSrcDstTables extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeDataExportPreCheckResult extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeDataExportTaskReport extends Rpc
{
}

/**
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeDataExportTasks extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeDataImportPreCheckResult extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeDataImportTaskReport extends Rpc
{
}

/**
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeDataImportTasks extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPassword()
 * @method $this withPassword($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 * @method string getDbInstanceId()
 * @method $this withDbInstanceId($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeDbInstanceDbs extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getSearch()
 * @method $this withSearch($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeDbInstances extends Rpc
{
}

/**
 * @method string getCommodityCode()
 * @method $this withCommodityCode($value)
 */
class DescribeDrdsComponents extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsDB extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstanceId()
 * @method $this withDbInstanceId($value)
 */
class DescribeDrdsDBCluster extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstanceId()
 * @method $this withDbInstanceId($value)
 */
class DescribeDrdsDbInstance extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeDrdsDbInstances extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getGroupName()
 * @method $this withGroupName($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsDBIpWhiteList extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsDbRdsNameList extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeDrdsDBs extends Rpc
{
}

/**
 * @method string getTaskType()
 * @method $this withTaskType($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsDbTasks extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsDbTestLink extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeDrdsInstance extends Rpc
{
}

/**
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getKey()
 * @method $this withKey($value)
 */
class DescribeDrdsInstanceDbMonitor extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeDrdsInstanceLevelTasks extends Rpc
{
}

/**
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getKey()
 * @method $this withKey($value)
 * @method string getPeriodMultiple()
 * @method $this withPeriodMultiple($value)
 */
class DescribeDrdsInstanceMonitor extends Rpc
{
}

/**
 * @method string getDescription()
 * @method $this withDescription($value)
 * @method string getType()
 * @method $this withType($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getResourceGroupId()
 * @method $this withResourceGroupId($value)
 * @method string getExpired()
 * @method $this withExpired($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 * @method array getTag()
 * @method string getMix()
 * @method $this withMix($value)
 */
class DescribeDrdsInstances extends Rpc
{

    /**
     * @param array $tag
     *
     * @return $this
     */
	public function withTag(array $tag)
	{
	    $this->data['Tag'] = $tag;
		foreach ($tag as $depth1 => $depth1Value) {
			if(isset($depth1Value['Value'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Value'] = $depth1Value['Value'];
			}
			if(isset($depth1Value['Key'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Key'] = $depth1Value['Key'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeDrdsInstanceVersion extends Rpc
{
}

/**
 * @method string getParamLevel()
 * @method $this withParamLevel($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsParams extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeDrdsRdsInstances extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbNamePattern()
 * @method $this withDbNamePattern($value)
 */
class DescribeDrdsShardingDbs extends Rpc
{
}

/**
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 * @method string getExeTime()
 * @method $this withExeTime($value)
 */
class DescribeDrdsSlowSqls extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeDrdsSqlAuditStatus extends Rpc
{
}

/**
 * @method string getTaskType()
 * @method $this withTaskType($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeDrdsTasks extends Rpc
{
}

/**
 * @method string getImportParam()
 * @method $this withImportParam($value)
 */
class DescribeEvaluateDataImportDbTopologys extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeEvaluatePreCheckResult extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeEvaluateTaskReport extends Rpc
{
}

/**
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeEvaluateTasks extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeExpandLogicTableInfoList extends Rpc
{
}

/**
 * @method string getOrderId()
 * @method $this withOrderId($value)
 */
class DescribeFirstDrdsInstanceByOrderId extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeGlobalBroadcastType extends Rpc
{
}

/**
 * @method string getHistoreInstanceId()
 * @method $this withHistoreInstanceId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeHiStoreInstanceInfo extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeHotDbList extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeInstanceAccounts extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeInstanceMenuSwitch extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeInstanceSwitchAzone extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeInstanceSwitchNetwork extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeInstDbLogInfo extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeInstDbSlsInfo extends Rpc
{
}

/**
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 */
class DescribeMyCatDbList extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeMyCatReportInfo extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribePreCheckResult extends Rpc
{
}

/**
 * @method string getCommodityCode()
 * @method $this withCommodityCode($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getOrderType()
 * @method $this withOrderType($value)
 */
class DescribeRdsCommodity extends Rpc
{
}

/**
 * @method string getRdsInstId()
 * @method $this withRdsInstId($value)
 * @method string getSearch()
 * @method $this withSearch($value)
 */
class DescribeRdsDbInfos extends Rpc
{
}

/**
 * @method array getRdsInstanceId()
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeRdsDrdsDB extends Rpc
{

    /**
     * @param array $rdsInstanceId
     *
     * @return $this
     */
	public function withRdsInstanceId(array $rdsInstanceId)
	{
	    $this->data['RdsInstanceId'] = $rdsInstanceId;
		foreach ($rdsInstanceId as $i => $iValue) {
			$this->options['query']['RdsInstanceId.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getPageNumber()
 * @method $this withPageNumber($value)
 * @method string getSearch()
 * @method $this withSearch($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeRdsInstInfos extends Rpc
{
}

/**
 * @method string getKeys()
 * @method $this withKeys($value)
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getRdsInstanceId()
 * @method $this withRdsInstanceId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeRDSPerformance extends Rpc
{
}

/**
 * @method array getRdsInstanceId()
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeRdsPerformanceSummary extends Rpc
{

    /**
     * @param array $rdsInstanceId
     *
     * @return $this
     */
	public function withRdsInstanceId(array $rdsInstanceId)
	{
	    $this->data['RdsInstanceId'] = $rdsInstanceId;
		foreach ($rdsInstanceId as $i => $iValue) {
			$this->options['query']['RdsInstanceId.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getRdsInstanceId()
 * @method $this withRdsInstanceId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeRdsReadOnly extends Rpc
{
}

/**
 * @method array getRdsInstance()
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class DescribeRdsSuperAccountInstances extends Rpc
{

    /**
     * @param array $rdsInstance
     *
     * @return $this
     */
	public function withRdsInstance(array $rdsInstance)
	{
	    $this->data['RdsInstance'] = $rdsInstance;
		foreach ($rdsInstance as $i => $iValue) {
			$this->options['query']['RdsInstance.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeRecycleBinStatus extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DescribeRecycleBinTables extends Rpc
{
}

/**
 * @method string getBackupDbNames()
 * @method $this withBackupDbNames($value)
 * @method string getBackupId()
 * @method $this withBackupId($value)
 * @method string getBackupLevel()
 * @method $this withBackupLevel($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPreferredBackupTime()
 * @method $this withPreferredBackupTime($value)
 * @method string getBackupMode()
 * @method $this withBackupMode($value)
 */
class DescribeRestoreOrder extends Rpc
{
}

/**
 * @method string getTargetTableName()
 * @method $this withTargetTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getSourceTableName()
 * @method $this withSourceTableName($value)
 */
class DescribeShardTaskInfo extends Rpc
{
}

/**
 * @method string getTaskType()
 * @method $this withTaskType($value)
 * @method string getQuery()
 * @method $this withQuery($value)
 * @method string getCurrentPage()
 * @method $this withCurrentPage($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeShardTaskList extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class DescribeSqlFlashbakTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 */
class DescribeTable extends Rpc
{
}

/**
 * @method string getTableType()
 * @method $this withTableType($value)
 * @method string getQuery()
 * @method $this withQuery($value)
 * @method string getCurrentPage()
 * @method $this withCurrentPage($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeTableListByType extends Rpc
{
}

/**
 * @method string getQuery()
 * @method $this withQuery($value)
 * @method string getCurrentPage()
 * @method $this withCurrentPage($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getPageSize()
 * @method $this withPageSize($value)
 */
class DescribeTables extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 */
class DescribeTableShardingInfo extends Rpc
{
}

/**
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class DescribeUnCompatibilitySql extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class DisableSqlAudit extends Rpc
{
}

/**
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class EnableInstanceIpv6Address extends Rpc
{
}

/**
 * @method string getRecallStartTimestamp()
 * @method $this withRecallStartTimestamp($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getIsRecall()
 * @method $this withIsRecall($value)
 * @method string getRecallEndTimestamp()
 * @method $this withRecallEndTimestamp($value)
 */
class EnableSqlAudit extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class EnableSqlFlashbackMatchSwitch extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 */
class FlashbackRecycleBinTable extends Rpc
{
}

/**
 * @method string getBatchEvaluateTaskId()
 * @method $this withBatchEvaluateTaskId($value)
 */
class GetBatchEvaluateTaskReport extends Rpc
{
}

/**
 * @method array getResourceId()
 * @method string getResourceType()
 * @method $this withResourceType($value)
 * @method string getNoRole()
 * @method $this withNoRole($value)
 * @method string getNextToken()
 * @method $this withNextToken($value)
 * @method array getTag()
 */
class ListTagResources extends Rpc
{

    /**
     * @param array $resourceId
     *
     * @return $this
     */
	public function withResourceId(array $resourceId)
	{
	    $this->data['ResourceId'] = $resourceId;
		foreach ($resourceId as $i => $iValue) {
			$this->options['query']['ResourceId.' . ($i + 1)] = $iValue;
		}

		return $this;
    }

    /**
     * @param array $tag
     *
     * @return $this
     */
	public function withTag(array $tag)
	{
	    $this->data['Tag'] = $tag;
		foreach ($tag as $depth1 => $depth1Value) {
			if(isset($depth1Value['Value'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Value'] = $depth1Value['Value'];
			}
			if(isset($depth1Value['Key'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Key'] = $depth1Value['Key'];
			}
		}

		return $this;
    }
}

/**
 * @method string getReportId()
 * @method $this withReportId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class ListUserReports extends Rpc
{
}

/**
 * @method string getDrdsVer()
 * @method $this withDrdsVer($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class ListVersions extends Rpc
{
}

/**
 * @method string getParams()
 * @method $this withParams($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDBInstanceId()
 * @method $this withDBInstanceId($value)
 * @method string getRdsAction()
 * @method $this withRdsAction($value)
 */
class ManagePrivateRds extends Rpc
{
}

/**
 * @method string getDescription()
 * @method $this withDescription($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 */
class ModifyAccountDescription extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 * @method array getDbPrivilege()
 */
class ModifyAccountPrivilege extends Rpc
{

    /**
     * @param array $dbPrivilege
     *
     * @return $this
     */
	public function withDbPrivilege(array $dbPrivilege)
	{
	    $this->data['DbPrivilege'] = $dbPrivilege;
		foreach ($dbPrivilege as $depth1 => $depth1Value) {
			if(isset($depth1Value['DbName'])){
				$this->options['query']['DbPrivilege.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['Privilege'])){
				$this->options['query']['DbPrivilege.' . ($depth1 + 1) . '.Privilege'] = $depth1Value['Privilege'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDescription()
 * @method $this withDescription($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class ModifyDrdsInstanceDescription extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getGroupName()
 * @method $this withGroupName($value)
 * @method string getMode()
 * @method $this withMode($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getGroupAttribute()
 * @method $this withGroupAttribute($value)
 * @method string getIpWhiteList()
 * @method $this withIpWhiteList($value)
 */
class ModifyDrdsIpWhiteList extends Rpc
{
}

/**
 * @method string getWeights()
 * @method $this withWeights($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbNodeIds()
 * @method $this withDbNodeIds($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstanceId()
 * @method $this withDbInstanceId($value)
 */
class ModifyPolarDbReadWeight extends Rpc
{
}

/**
 * @method string getWeights()
 * @method $this withWeights($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getInstanceNames()
 * @method $this withInstanceNames($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class ModifyRdsReadWeight extends Rpc
{
}

/**
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 */
class MyCatConnectTest extends Rpc
{
}

/**
 * @method string getDstPort()
 * @method $this withDstPort($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getDstUser()
 * @method $this withDstUser($value)
 * @method string getDstDbNme()
 * @method $this withDstDbNme($value)
 * @method string getTableMap()
 * @method $this withTableMap($value)
 * @method string getImportDb()
 * @method $this withImportDb($value)
 * @method string getDstPwd()
 * @method $this withDstPwd($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 * @method string getDstDrdsInstanceId()
 * @method $this withDstDrdsInstanceId($value)
 */
class MyCatCustomImportPreCheck extends Rpc
{
}

/**
 * @method string getTbPartitions()
 * @method $this withTbPartitions($value)
 * @method string getIsShard()
 * @method $this withIsShard($value)
 * @method string getTbShardingFunction()
 * @method $this withTbShardingFunction($value)
 * @method string getDbShardingColumnList()
 * @method $this withDbShardingColumnList($value)
 * @method string getTbComputeLength()
 * @method $this withTbComputeLength($value)
 * @method string getTbRightShiftOffset()
 * @method $this withTbRightShiftOffset($value)
 * @method string getDbComputeLength()
 * @method $this withDbComputeLength($value)
 * @method string getSrcTableName()
 * @method $this withSrcTableName($value)
 * @method string getDbRightShiftOffset()
 * @method $this withDbRightShiftOffset($value)
 * @method string getTargetTableName()
 * @method $this withTargetTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbShardingFunction()
 * @method $this withDbShardingFunction($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTbShardingColumnList()
 * @method $this withTbShardingColumnList($value)
 */
class PreCheckModifyTableShardingKeyParam extends Rpc
{
}

/**
 * @method string getSequenceType()
 * @method $this withSequenceType($value)
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getSchemaUrl()
 * @method $this withSchemaUrl($value)
 * @method string getBusPeakQps()
 * @method $this withBusPeakQps($value)
 * @method string getQpsIncPercent()
 * @method $this withQpsIncPercent($value)
 * @method string getServerUrl()
 * @method $this withServerUrl($value)
 * @method string getCapacityIncPercent()
 * @method $this withCapacityIncPercent($value)
 * @method string getRuleUrl()
 * @method $this withRuleUrl($value)
 * @method array getImportDbList()
 */
class PrecheckMyCatEvaluate extends Rpc
{

    /**
     * @param array $importDbList
     *
     * @return $this
     */
	public function withImportDbList(array $importDbList)
	{
	    $this->data['ImportDbList'] = $importDbList;
		foreach ($importDbList as $i => $iValue) {
			$this->options['query']['ImportDbList.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getBackupDbNames()
 * @method $this withBackupDbNames($value)
 * @method string getBackupLevel()
 * @method $this withBackupLevel($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getBackupMode()
 * @method $this withBackupMode($value)
 */
class PutStartBackup extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class RefreshDrdsAtomUrl extends Rpc
{
}

/**
 * @method string getHistoreInstanceId()
 * @method $this withHistoreInstanceId($value)
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class ReleaseHiStoreInstance extends Rpc
{
}

/**
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class ReleaseInstanceInternetAddress extends Rpc
{
}

/**
 * @method string getBackupId()
 * @method $this withBackupId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class RemoveBackupsSet extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class RemoveDataExportTask extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class RemoveDataImportTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class RemoveDrdsDb extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class RemoveDrdsDbFailedRecord extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class RemoveDrdsInstance extends Rpc
{
}

/**
 * @method string getRoDbInstanceId()
 * @method $this withRoDbInstanceId($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstanceId()
 * @method $this withDbInstanceId($value)
 * @method string getForce()
 * @method $this withForce($value)
 */
class RemoveDrdsMysql extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class RemoveEvaluateTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getAccountName()
 * @method $this withAccountName($value)
 */
class RemoveInstanceAccount extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 */
class RemoveRecycleBinTable extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class ResetDrdsToRdsConnections extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class RestartDrdsInstance extends Rpc
{
}

/**
 * @method string getHistoreInstanceId()
 * @method $this withHistoreInstanceId($value)
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class RollbackHiStoreInstance extends Rpc
{
}

/**
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class RollbackInstanceVersion extends Rpc
{
}

/**
 * @method string getLocalLogRetentionHours()
 * @method $this withLocalLogRetentionHours($value)
 * @method string getLocalLogRetentionSpace()
 * @method $this withLocalLogRetentionSpace($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getHighSpaceUsageProtection()
 * @method $this withHighSpaceUsageProtection($value)
 */
class SetBackupLocal extends Rpc
{
}

/**
 * @method string getBackupDbNames()
 * @method $this withBackupDbNames($value)
 * @method string getBackupLog()
 * @method $this withBackupLog($value)
 * @method string getPreferredBackupEndTime()
 * @method $this withPreferredBackupEndTime($value)
 * @method string getPreferredBackupPeriod()
 * @method $this withPreferredBackupPeriod($value)
 * @method string getPreferredBackupStartTime()
 * @method $this withPreferredBackupStartTime($value)
 * @method string getBackupLevel()
 * @method $this withBackupLevel($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDataBackupRetentionPeriod()
 * @method $this withDataBackupRetentionPeriod($value)
 * @method string getBackupMode()
 * @method $this withBackupMode($value)
 * @method string getLogBackupRetentionPeriod()
 * @method $this withLogBackupRetentionPeriod($value)
 */
class SetBackupPolicy extends Rpc
{
}

/**
 * @method string getActive()
 * @method $this withActive($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method array getTableName()
 */
class SetupBroadcastTables extends Rpc
{

    /**
     * @param array $tableName
     *
     * @return $this
     */
	public function withTableName(array $tableName)
	{
	    $this->data['TableName'] = $tableName;
		foreach ($tableName as $i => $iValue) {
			$this->options['query']['TableName.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getParamLevel()
 * @method $this withParamLevel($value)
 * @method array getData()
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class SetupDrdsParams extends Rpc
{

    /**
     * @param array $data
     *
     * @return $this
     */
	public function withData(array $data)
	{
	    $this->data['Data'] = $data;
		foreach ($data as $depth1 => $depth1Value) {
			if(isset($depth1Value['ParamType'])){
				$this->options['query']['Data.' . ($depth1 + 1) . '.ParamType'] = $depth1Value['ParamType'];
			}
			if(isset($depth1Value['DbName'])){
				$this->options['query']['Data.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['ParamRanges'])){
				$this->options['query']['Data.' . ($depth1 + 1) . '.ParamRanges'] = $depth1Value['ParamRanges'];
			}
			if(isset($depth1Value['ParamVariableName'])){
				$this->options['query']['Data.' . ($depth1 + 1) . '.ParamVariableName'] = $depth1Value['ParamVariableName'];
			}
			if(isset($depth1Value['ParamValue'])){
				$this->options['query']['Data.' . ($depth1 + 1) . '.ParamValue'] = $depth1Value['ParamValue'];
			}
		}

		return $this;
    }
}

/**
 * @method string getStatusAction()
 * @method $this withStatusAction($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class SetupRecycleBinStatus extends Rpc
{
}

/**
 * @method string getAllowFullTableScan()
 * @method $this withAllowFullTableScan($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method array getTableName()
 */
class SetupTable extends Rpc
{

    /**
     * @param array $tableName
     *
     * @return $this
     */
	public function withTableName(array $tableName)
	{
	    $this->data['TableName'] = $tableName;
		foreach ($tableName as $i => $iValue) {
			$this->options['query']['TableName.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class SqlCompatibilityCancel extends Rpc
{
}

/**
 * @method string getTargetVersion()
 * @method $this withTargetVersion($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPerformanceTest()
 * @method $this withPerformanceTest($value)
 */
class SqlCompatibilityStart extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class StartDataImportTask extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class StartEvaluateTask extends Rpc
{
}

/**
 * @method string getBackupDbNames()
 * @method $this withBackupDbNames($value)
 * @method string getBackupId()
 * @method $this withBackupId($value)
 * @method string getBackupLevel()
 * @method $this withBackupLevel($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getPreferredBackupTime()
 * @method $this withPreferredBackupTime($value)
 * @method string getBackupMode()
 * @method $this withBackupMode($value)
 */
class StartRestore extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class StopDataExportTask extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class StopDataImportTask extends Rpc
{
}

/**
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class StopEvaluateTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class SubmitCleanTableShardingKeyModify extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getExpandType()
 * @method $this withExpandType($value)
 * @method string getJobId()
 * @method $this withJobId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getParentJobId()
 * @method $this withParentJobId($value)
 */
class SubmitCleanTask extends Rpc
{
}

/**
 * @method array getTableList()
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class SubmitHotExpandPreCheckTask extends Rpc
{

    /**
     * @param array $tableList
     *
     * @return $this
     */
	public function withTableList(array $tableList)
	{
	    $this->data['TableList'] = $tableList;
		foreach ($tableList as $i => $iValue) {
			$this->options['query']['TableList.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method array getMapping()
 * @method string getTaskDesc()
 * @method $this withTaskDesc($value)
 * @method array getSupperAccountMapping()
 * @method array getExtendedMapping()
 * @method string getTaskName()
 * @method $this withTaskName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method array getInstanceDbMapping()
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class SubmitHotExpandTask extends Rpc
{

    /**
     * @param array $mapping
     *
     * @return $this
     */
	public function withMapping(array $mapping)
	{
	    $this->data['Mapping'] = $mapping;
		foreach ($mapping as $depth1 => $depth1Value) {
			if(isset($depth1Value['DbShardColumn'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.DbShardColumn'] = $depth1Value['DbShardColumn'];
			}
			if(isset($depth1Value['TbShardColumn'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.TbShardColumn'] = $depth1Value['TbShardColumn'];
			}
			if(isset($depth1Value['ShardTbValue'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.ShardTbValue'] = $depth1Value['ShardTbValue'];
			}
			if(isset($depth1Value['HotDbName'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.HotDbName'] = $depth1Value['HotDbName'];
			}
			if(isset($depth1Value['ShardDbValue'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.ShardDbValue'] = $depth1Value['ShardDbValue'];
			}
			if(isset($depth1Value['HotTableName'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.HotTableName'] = $depth1Value['HotTableName'];
			}
			if(isset($depth1Value['LogicTable'])){
				$this->options['query']['Mapping.' . ($depth1 + 1) . '.LogicTable'] = $depth1Value['LogicTable'];
			}
		}

		return $this;
    }

    /**
     * @param array $supperAccountMapping
     *
     * @return $this
     */
	public function withSupperAccountMapping(array $supperAccountMapping)
	{
	    $this->data['SupperAccountMapping'] = $supperAccountMapping;
		foreach ($supperAccountMapping as $depth1 => $depth1Value) {
			if(isset($depth1Value['InstanceName'])){
				$this->options['query']['SupperAccountMapping.' . ($depth1 + 1) . '.InstanceName'] = $depth1Value['InstanceName'];
			}
			if(isset($depth1Value['SupperAccount'])){
				$this->options['query']['SupperAccountMapping.' . ($depth1 + 1) . '.SupperAccount'] = $depth1Value['SupperAccount'];
			}
			if(isset($depth1Value['SupperPassword'])){
				$this->options['query']['SupperAccountMapping.' . ($depth1 + 1) . '.SupperPassword'] = $depth1Value['SupperPassword'];
			}
		}

		return $this;
    }

    /**
     * @param array $extendedMapping
     *
     * @return $this
     */
	public function withExtendedMapping(array $extendedMapping)
	{
	    $this->data['ExtendedMapping'] = $extendedMapping;
		foreach ($extendedMapping as $depth1 => $depth1Value) {
			if(isset($depth1Value['SrcInstanceId'])){
				$this->options['query']['ExtendedMapping.' . ($depth1 + 1) . '.SrcInstanceId'] = $depth1Value['SrcInstanceId'];
			}
			if(isset($depth1Value['SrcDb'])){
				$this->options['query']['ExtendedMapping.' . ($depth1 + 1) . '.SrcDb'] = $depth1Value['SrcDb'];
			}
		}

		return $this;
    }

    /**
     * @param array $instanceDbMapping
     *
     * @return $this
     */
	public function withInstanceDbMapping(array $instanceDbMapping)
	{
	    $this->data['InstanceDbMapping'] = $instanceDbMapping;
		foreach ($instanceDbMapping as $depth1 => $depth1Value) {
			if(isset($depth1Value['DbList'])){
				$this->options['query']['InstanceDbMapping.' . ($depth1 + 1) . '.DbList'] = $depth1Value['DbList'];
			}
			if(isset($depth1Value['InstanceName'])){
				$this->options['query']['InstanceDbMapping.' . ($depth1 + 1) . '.InstanceName'] = $depth1Value['InstanceName'];
			}
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class SubmitRollbackShardingKeyModify extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getExpandType()
 * @method $this withExpandType($value)
 * @method string getJobId()
 * @method $this withJobId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getParentJobId()
 * @method $this withParentJobId($value)
 */
class SubmitRollbackTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getDbInstType()
 * @method $this withDbInstType($value)
 */
class SubmitSmoothExpandPreCheck extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class SubmitSmoothExpandPreCheckTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbInstanceIsCreating()
 * @method $this withDbInstanceIsCreating($value)
 * @method array getRdsSuperInstances()
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method array getTransferTaskInfos()
 */
class SubmitSmoothExpandTask extends Rpc
{

    /**
     * @param array $rdsSuperInstances
     *
     * @return $this
     */
	public function withRdsSuperInstances(array $rdsSuperInstances)
	{
	    $this->data['RdsSuperInstances'] = $rdsSuperInstances;
		foreach ($rdsSuperInstances as $depth1 => $depth1Value) {
			if(isset($depth1Value['Password'])){
				$this->options['query']['RdsSuperInstances.' . ($depth1 + 1) . '.Password'] = $depth1Value['Password'];
			}
			if(isset($depth1Value['AccountName'])){
				$this->options['query']['RdsSuperInstances.' . ($depth1 + 1) . '.AccountName'] = $depth1Value['AccountName'];
			}
			if(isset($depth1Value['RdsName'])){
				$this->options['query']['RdsSuperInstances.' . ($depth1 + 1) . '.RdsName'] = $depth1Value['RdsName'];
			}
		}

		return $this;
    }

    /**
     * @param array $transferTaskInfos
     *
     * @return $this
     */
	public function withTransferTaskInfos(array $transferTaskInfos)
	{
	    $this->data['TransferTaskInfos'] = $transferTaskInfos;
		foreach ($transferTaskInfos as $depth1 => $depth1Value) {
			if(isset($depth1Value['DbName'])){
				$this->options['query']['TransferTaskInfos.' . ($depth1 + 1) . '.DbName'] = $depth1Value['DbName'];
			}
			if(isset($depth1Value['SrcInstanceName'])){
				$this->options['query']['TransferTaskInfos.' . ($depth1 + 1) . '.SrcInstanceName'] = $depth1Value['SrcInstanceName'];
			}
			if(isset($depth1Value['InstanceType'])){
				$this->options['query']['TransferTaskInfos.' . ($depth1 + 1) . '.InstanceType'] = $depth1Value['InstanceType'];
			}
			if(isset($depth1Value['DstInstanceName'])){
				$this->options['query']['TransferTaskInfos.' . ($depth1 + 1) . '.DstInstanceName'] = $depth1Value['DstInstanceName'];
			}
		}

		return $this;
    }
}

/**
 * @method string getTraceId()
 * @method $this withTraceId($value)
 * @method string getSqlPk()
 * @method $this withSqlPk($value)
 * @method string getStartTime()
 * @method $this withStartTime($value)
 * @method string getRecallRestoreType()
 * @method $this withRecallRestoreType($value)
 * @method string getTableName()
 * @method $this withTableName($value)
 * @method string getSqlType()
 * @method $this withSqlType($value)
 * @method string getEndTime()
 * @method $this withEndTime($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getRecallType()
 * @method $this withRecallType($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class SubmitSqlFlashbackTask extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTaskId()
 * @method $this withTaskId($value)
 */
class SubmitSwitchTableShardingKeyModify extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getExpandType()
 * @method $this withExpandType($value)
 * @method string getJobId()
 * @method $this withJobId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getParentJobId()
 * @method $this withParentJobId($value)
 */
class SubmitSwitchTask extends Rpc
{
}

/**
 * @method string getTbPartitions()
 * @method $this withTbPartitions($value)
 * @method string getIsShard()
 * @method $this withIsShard($value)
 * @method string getTbShardingFunction()
 * @method $this withTbShardingFunction($value)
 * @method string getDbShardingColumnList()
 * @method $this withDbShardingColumnList($value)
 * @method string getTbComputeLength()
 * @method $this withTbComputeLength($value)
 * @method string getTbRightShiftOffset()
 * @method $this withTbRightShiftOffset($value)
 * @method string getDbComputeLength()
 * @method $this withDbComputeLength($value)
 * @method string getSrcTableName()
 * @method $this withSrcTableName($value)
 * @method string getDbRightShiftOffset()
 * @method $this withDbRightShiftOffset($value)
 * @method string getTargetTableName()
 * @method $this withTargetTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbShardingFunction()
 * @method $this withDbShardingFunction($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getTbShardingColumnList()
 * @method $this withTbShardingColumnList($value)
 */
class SubmitTableShardingKeyModify extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 */
class SwitchGlobalBroadcastType extends Rpc
{
}

/**
 * @method array getResourceId()
 * @method string getResourceType()
 * @method $this withResourceType($value)
 * @method string getNoRole()
 * @method $this withNoRole($value)
 * @method array getTag()
 */
class TagResources extends Rpc
{

    /**
     * @param array $resourceId
     *
     * @return $this
     */
	public function withResourceId(array $resourceId)
	{
	    $this->data['ResourceId'] = $resourceId;
		foreach ($resourceId as $i => $iValue) {
			$this->options['query']['ResourceId.' . ($i + 1)] = $iValue;
		}

		return $this;
    }

    /**
     * @param array $tag
     *
     * @return $this
     */
	public function withTag(array $tag)
	{
	    $this->data['Tag'] = $tag;
		foreach ($tag as $depth1 => $depth1Value) {
			if(isset($depth1Value['Value'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Value'] = $depth1Value['Value'];
			}
			if(isset($depth1Value['Key'])){
				$this->options['query']['Tag.' . ($depth1 + 1) . '.Key'] = $depth1Value['Key'];
			}
		}

		return $this;
    }
}

/**
 * @method string getAll()
 * @method $this withAll($value)
 * @method array getResourceId()
 * @method string getResourceType()
 * @method $this withResourceType($value)
 * @method string getNoRole()
 * @method $this withNoRole($value)
 * @method array getTagKey()
 */
class UntagResources extends Rpc
{

    /**
     * @param array $resourceId
     *
     * @return $this
     */
	public function withResourceId(array $resourceId)
	{
	    $this->data['ResourceId'] = $resourceId;
		foreach ($resourceId as $i => $iValue) {
			$this->options['query']['ResourceId.' . ($i + 1)] = $iValue;
		}

		return $this;
    }

    /**
     * @param array $tagKey
     *
     * @return $this
     */
	public function withTagKey(array $tagKey)
	{
	    $this->data['TagKey'] = $tagKey;
		foreach ($tagKey as $i => $iValue) {
			$this->options['query']['TagKey.' . ($i + 1)] = $iValue;
		}

		return $this;
    }
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getRetainClassic()
 * @method $this withRetainClassic($value)
 * @method string getClassicExpiredDays()
 * @method $this withClassicExpiredDays($value)
 * @method string getSrcInstanceNetworkType()
 * @method $this withSrcInstanceNetworkType($value)
 */
class UpdateInstanceNetwork extends Rpc
{
}

/**
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getNewResourceGroupId()
 * @method $this withNewResourceGroupId($value)
 */
class UpdateResourceGroupAttribute extends Rpc
{
}

/**
 * @method string getHistoreInstanceId()
 * @method $this withHistoreInstanceId($value)
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 */
class UpgradeHiStoreInstance extends Rpc
{
}

/**
 * @method string getDrdsPassword()
 * @method $this withDrdsPassword($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getRpm()
 * @method $this withRpm($value)
 */
class UpgradeInstanceVersion extends Rpc
{
}

/**
 * @method string getTaskType()
 * @method $this withTaskType($value)
 * @method string getTargetTableName()
 * @method $this withTargetTableName($value)
 * @method string getDrdsInstanceId()
 * @method $this withDrdsInstanceId($value)
 * @method string getDbName()
 * @method $this withDbName($value)
 * @method string getSourceTableName()
 * @method $this withSourceTableName($value)
 */
class ValidateShardTask extends Rpc
{
}
