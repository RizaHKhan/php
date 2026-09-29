import { Stack, StackProps } from "aws-cdk-lib/core";
import { AutoScalingGroup } from "aws-cdk-lib/aws-autoscaling";
import {
  IRole,
  ManagedPolicy,
  Role,
  ServicePrincipal,
} from "aws-cdk-lib/aws-iam";
import {
  Instance,
  InstanceClass,
  InstanceSize,
  InstanceType,
  IVpc,
  MachineImage,
  SecurityGroup,
  Subnet,
  UserData,
} from "aws-cdk-lib/aws-ec2";

interface ComputeProps extends StackProps {}

interface InstanceConfig {
  name: string;
  vpc: IVpc;
  instanceType?: InstanceType;
  securityGroup?: SecurityGroup;
  subnets?: Subnet | Subnet[];
  userData?: UserData;
  role?: IRole;
  associatePublicIpAddress?: boolean;
  privateIpAddress?: string;
}

interface AutoScalingGroupConfig extends InstanceConfig {
  minCapacity?: number;
  maxCapacity?: number;
  desiredCapacity?: number;
}

const selectedSubnets = (subnets?: Subnet | Subnet[]) =>
  subnets ? (Array.isArray(subnets) ? subnets : [subnets]) : undefined;

const ssmRole = (stack: Stack, name: string, role?: IRole) =>
  role ??
  new Role(stack, `${name}SsmRole`, {
    assumedBy: new ServicePrincipal("ec2.amazonaws.com"),
    managedPolicies: [
      ManagedPolicy.fromAwsManagedPolicyName("AmazonSSMManagedInstanceCore"),
    ],
  });

export function compute(stack: Stack, props: ComputeProps = {}) {
  // Example: const webInstance = instance({ name: "WebInstance", vpc: defaultVpc, securityGroup: webSg, subnets: publicSubnet, userData });
  const instance = ({
    name,
    vpc,
    instanceType,
    securityGroup,
    subnets,
    userData,
    role,
    associatePublicIpAddress,
    privateIpAddress,
  }: InstanceConfig): Instance => {
    const subnetsToUse = selectedSubnets(subnets);
    const vpcSubnets = subnetsToUse ? { subnets: subnetsToUse } : undefined;
    const instanceRole = ssmRole(stack, name, role);

    return new Instance(stack, name, {
      vpc,
      vpcSubnets,
      instanceType:
        instanceType ?? InstanceType.of(InstanceClass.T3, InstanceSize.MICRO),
      machineImage: MachineImage.latestAmazonLinux2023(),
      securityGroup,
      userData,
      role: instanceRole,
      associatePublicIpAddress,
      privateIpAddress,
      requireImdsv2: true,
    });
  };

  // Example: const webAsg = autoScalingGroup({ name: "WebAutoScalingGroup", vpc: defaultVpc, securityGroup: webSg, subnets: publicSubnet, userData, minCapacity: 2, maxCapacity: 3 });
  const autoScalingGroup = ({
    name,
    vpc,
    instanceType,
    securityGroup,
    subnets,
    userData,
    role,
    associatePublicIpAddress,
    minCapacity,
    maxCapacity,
    desiredCapacity,
  }: AutoScalingGroupConfig): AutoScalingGroup => {
    const subnetsToUse = selectedSubnets(subnets);
    const vpcSubnets = subnetsToUse ? { subnets: subnetsToUse } : undefined;
    const instanceRole = ssmRole(stack, name, role);

    return new AutoScalingGroup(stack, name, {
      vpc,
      vpcSubnets,
      instanceType:
        instanceType ?? InstanceType.of(InstanceClass.T3, InstanceSize.MICRO),
      machineImage: MachineImage.latestAmazonLinux2023(),
      securityGroup,
      userData,
      role: instanceRole,
      associatePublicIpAddress,
      requireImdsv2: true,
      minCapacity: minCapacity ?? 1,
      maxCapacity: maxCapacity ?? 1,
      desiredCapacity: desiredCapacity ?? minCapacity ?? 1,
    });
  };

  return { instance, autoScalingGroup };
}
